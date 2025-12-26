<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TwilioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AuthController extends Controller
{
    protected $twilioService;

    public function __construct(TwilioService $twilioService)
    {
        $this->twilioService = $twilioService;
    }

    public function showLoginForm()
    {
        if (session('user_id')) {
            return redirect('/matches');
        }
        return view('auth.login');
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'name' => 'required|string|max:255',
        ]);

        try {
            $originalPhone = $request->phone;
            $phone = $this->formatPhone($request->phone);

            // VALIDATION STRICTE: Vérifier que le numéro est autorisé
            if (!$this->isPhoneAllowedForPublic($phone)) {
                Log::warning('Tentative d\'inscription avec un numéro non autorisé', [
                    'phone' => $phone,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Ce numéro n\'est pas autorisé. Seuls les numéros ivoiriens (+225), sénégalais (+221) et français (+33) sont acceptés.',
                ], 403);
            }

            Log::info('=== ENVOI OTP SMS TWILIO ===', [
                'original_phone' => $originalPhone,
                'formatted_phone' => $phone,
                'name' => $request->name,
            ]);

            // Stocker le nom en cache pour la vérification ultérieure
            $cacheKey = 'otp_name_' . $phone;
            Cache::put($cacheKey, $request->name, now()->addMinutes(10));

            // Envoyer le code via Twilio Verify
            $result = $this->twilioService->sendVerificationCode($phone);

            if ($result['success']) {
                Log::info('Code OTP envoyé via Twilio', ['phone' => $phone, 'status' => $result['status'] ?? 'pending']);

                return response()->json([
                    'success' => true,
                    'message' => 'Code envoyé par SMS !',
                    'phone' => $phone,
                ]);
            } else {
                Log::error('Échec envoi OTP Twilio', ['phone' => $phone, 'error' => $result['error'] ?? 'Erreur inconnue']);

                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l\'envoi du SMS. Veuillez réessayer.',
                    'error' => $result['error'] ?? 'Erreur inconnue',
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Exception sendOtp', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur technique. Réessayez.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'code' => 'required|string|size:6',
        ]);

        try {
            $phone = $this->formatPhone($request->phone);

            // Double vérification: le numéro doit être autorisé
            if (!$this->isPhoneAllowedForPublic($phone)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce numéro n\'est pas autorisé.',
                ], 403);
            }

            Log::info('=== VÉRIFICATION OTP TWILIO ===', ['phone' => $phone]);

            // Vérifier le code via Twilio Verify
            $result = $this->twilioService->verifyCode($phone, $request->code);

            if (!$result['success'] || !$result['valid']) {
                Log::warning('Code OTP incorrect', ['phone' => $phone, 'status' => $result['status'] ?? 'failed']);

                return response()->json([
                    'success' => false,
                    'message' => 'Code incorrect ou expiré. Veuillez réessayer.',
                ], 400);
            }

            // Récupérer le nom depuis le cache
            $cacheKey = 'otp_name_' . $phone;
            $name = Cache::get($cacheKey, 'User ' . substr($phone, -4));
            Cache::forget($cacheKey);

            $user = User::where('phone', $phone)->first();

            if (!$user) {
                $user = User::create([
                    'name' => $name,
                    'phone' => $phone,
                    'password' => Hash::make(Str::random(32)),
                    'last_login_at' => now(),
                ]);
                Log::info('Nouvel utilisateur créé', ['user_id' => $user->id, 'phone' => $phone]);
            } else {
                if ($user->name !== $name && $name !== 'User ' . substr($phone, -4)) {
                    $user->update(['name' => $name]);
                }
                $user->update(['last_login_at' => now()]);
            }

            // Bonus connexion quotidienne (+1 point/jour)
            $pointsService = app(\App\Services\PointsService::class);
            $pointsService->awardDailyLoginPoints($user);
            
            // Recharger l'utilisateur pour avoir les points mis à jour
            $user->refresh();

            session([
                'user_id' => $user->id,
                'user_points' => $user->points_total ?? 0,
                'predictor_name' => $user->name
            ]);

            Log::info('Connexion réussie', ['user_id' => $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'Connexion réussie !',
                'redirect' => '/matches',
            ]);

        } catch (\Exception $e) {
            Log::error('Exception verifyOtp', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur technique. Réessayez.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Vérifie si un numéro est autorisé pour l'inscription publique
     */
    private function isPhoneAllowedForPublic(string $phone): bool
    {
        // Les numéros ivoiriens sont autorisés
        if (str_starts_with($phone, '+225')) {
            return true;
        }

        // Les numéros sénégalais sont autorisés
        if (str_starts_with($phone, '+221')) {
            return true;
        }

        // Les numéros français sont autorisés
        if (str_starts_with($phone, '+33')) {
            return true;
        }

        // Vérifier si le numéro est dans une whitelist pour les tests
        $testPhonesCI = config('auth_phones.test_phones_ci', []);
        if (in_array($phone, $testPhonesCI)) {
            Log::info('Numéro autorisé en mode test', ['phone' => $phone]);
            return true;
        }

        return false;
    }

    private function formatPhone(string $phone): string
    {
        $phone = preg_replace('/[^\d+]/', '', $phone);

        if (str_starts_with($phone, '+')) {
            return $phone;
        }

        if (str_starts_with($phone, '00')) {
            return '+' . substr($phone, 2);
        }

        // CI: 10 chiffres avec 0 initial -> +225
        if (strlen($phone) === 10 && str_starts_with($phone, '0')) {
            return '+225' . $phone;
        }

        // France: 10 chiffres commençant par 06 ou 07 -> +33
        if (strlen($phone) === 10 && (str_starts_with($phone, '06') || str_starts_with($phone, '07'))) {
            return '+33' . substr($phone, 1);
        }

        // SN: 9 chiffres commençant par 7 -> +221
        if (strlen($phone) === 9 && str_starts_with($phone, '7')) {
            return '+221' . $phone;
        }

        // Par défaut: assumer Côte d'Ivoire
        return '+225' . $phone;
    }

    public function logout(Request $request)
    {
        session()->forget('user_id');
        return redirect('/')->with('message', 'Vous avez été déconnecté.');
    }
}
