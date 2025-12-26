<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AdminOtpLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        // Si déjà connecté via session
        if (session('user_id')) {
            return redirect('/matches');
        }

        // Tentative de reconnexion via cookie "remember_token"
        $rememberToken = request()->cookie('remember_token');
        if ($rememberToken) {
            $user = User::where('remember_token', $rememberToken)->first();
            if ($user) {
                // Bonus connexion quotidienne (+1 point/jour)
                $pointsService = app(\App\Services\PointsService::class);
                $pointsService->awardDailyLoginPoints($user);
                
                // Recharger l'utilisateur pour avoir les points mis à jour
                $user->refresh();
                
                // Reconnecter automatiquement
                session([
                    'user_id' => $user->id,
                    'user_points' => $user->points_total ?? 0,
                    'predictor_name' => $user->name
                ]);
                $user->update(['last_login_at' => now()]);
                
                Log::info('Reconnexion automatique via remember_token', ['user_id' => $user->id]);
                return redirect('/matches');
            }
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

            // RATE LIMITING: 1 OTP par heure par numéro
            $rateLimitKey = 'otp_rate_limit_' . md5($phone);
            $lastOtpSent = Cache::get($rateLimitKey);
            
            if ($lastOtpSent) {
                $minutesRemaining = now()->diffInMinutes($lastOtpSent->addHour(), false);
                
                if ($minutesRemaining > 0) {
                    Log::warning('Rate limit OTP atteint', [
                        'phone' => $phone,
                        'minutes_remaining' => $minutesRemaining,
                    ]);
                    
                    return response()->json([
                        'success' => false,
                        'message' => "Vous avez déjà demandé un code. Veuillez attendre {$minutesRemaining} minute(s) avant de réessayer.",
                        'rate_limited' => true,
                        'minutes_remaining' => $minutesRemaining,
                    ], 429);
                }
            }

            Log::info('=== ENVOI OTP SMS ===', [
                'original_phone' => $originalPhone,
                'formatted_phone' => $phone,
                'name' => $request->name,
            ]);

            $otpCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            $cacheKey = 'otp_' . $phone;
            Cache::put($cacheKey, [
                'code' => $otpCode,
                'name' => $request->name,
                'phone' => $phone,
                'attempts' => 0,
            ], now()->addHour());

            // SÉCURITÉ: Ne jamais logger le code OTP en production
            Log::info('Code OTP genere', ['phone' => $phone]);

            $result = $this->sendSMS($phone, $otpCode);

            // Enregistrer le rate limit (1 heure)
            if ($result['success']) {
                Cache::put($rateLimitKey, now(), now()->addHour());
            }

            // Enregistrer le log OTP
            $otpLog = AdminOtpLog::create([
                'phone' => $phone,
                'code' => $otpCode,
                'whatsapp_number' => $phone, // Pour compatibilité, on garde le même champ
                'status' => $result['success'] ? 'sent' : 'failed',
                'otp_sent_at' => now(),
                'error_message' => $result['success'] ? null : ($result['error'] ?? 'Erreur inconnue'),
            ]);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Code envoyé par SMS !',
                    'phone' => $phone,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l\'envoi du SMS.',
                    'error' => $result['error'] ?? 'Erreur inconnue',
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Exception sendOtp', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur technique. Reessayez.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Envoie un SMS via Twilio
     */
    private function sendSMS(string $phone, string $otpCode): array
    {
        Log::info('=== DEBUT sendSMS (Twilio) ===');

        $accountSid = config('services.twilio.account_sid');
        $authToken = config('services.twilio.auth_token');
        $fromNumber = config('services.twilio.from_number');

        Log::info('Configuration Twilio', [
            'account_sid' => $accountSid ? substr($accountSid, 0, 10) . '...' : 'NULL',
            'from_number' => $fromNumber,
        ]);

        if (!$accountSid || !$authToken || !$fromNumber) {
            Log::error('Configuration Twilio incomplete !');
            return ['success' => false, 'error' => 'Configuration Twilio incomplete'];
        }

        // Formater le numéro au format international avec +
        $toNumber = '+' . ltrim($phone, '+');

        $message = "SOBOA FOOT TIME - Votre code de verification: {$otpCode}";

        try {
            Log::info('Envoi SMS via Twilio...', [
                'to' => $toNumber,
                'from' => $fromNumber,
            ]);

            $url = "https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json";

            $response = Http::withBasicAuth($accountSid, $authToken)
                ->asForm()
                ->timeout(30)
                ->post($url, [
                    'To' => $toNumber,
                    'From' => $fromNumber,
                    'Body' => $message,
                ]);

            Log::info('Reponse Twilio recue', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('=== SUCCES SMS Twilio ===', ['sid' => $data['sid'] ?? null]);
                return ['success' => true, 'sid' => $data['sid'] ?? null];
            } else {
                $errorData = $response->json();
                $errorMessage = $errorData['message'] ?? ('HTTP ' . $response->status());
                Log::error('=== ECHEC SMS Twilio ===', [
                    'status' => $response->status(),
                    'error' => $errorMessage,
                    'body' => $response->body(),
                ]);
                return ['success' => false, 'error' => $errorMessage];
            }
        } catch (\Exception $e) {
            Log::error('=== EXCEPTION Twilio ===', [
                'message' => $e->getMessage(),
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
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

            $cacheKey = 'otp_' . $phone;
            $otpData = Cache::get($cacheKey);

            // Récupérer le log OTP pour mise à jour
            $otpLog = AdminOtpLog::where('phone', $phone)
                ->where('status', 'sent')
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$otpData) {
                // Mettre à jour le log si trouvé
                if ($otpLog) {
                    $otpLog->update([
                        'status' => 'expired',
                        'verification_attempts' => ($otpLog->verification_attempts ?? 0) + 1,
                    ]);
                }
                return response()->json([
                    'success' => false,
                    'message' => 'Code expire. Veuillez renvoyer un nouveau code.',
                ], 400);
            }

            if ($otpData['attempts'] >= 5) {
                Cache::forget($cacheKey);
                // Mettre à jour le log
                if ($otpLog) {
                    $otpLog->update([
                        'status' => 'failed',
                        'verification_attempts' => 5,
                        'error_message' => 'Trop de tentatives',
                    ]);
                }
                return response()->json([
                    'success' => false,
                    'message' => 'Trop de tentatives. Veuillez renvoyer un nouveau code.',
                ], 400);
            }

            $otpData['attempts']++;
            Cache::put($cacheKey, $otpData, now()->addHour());

            if ($otpData['code'] !== $request->code) {
                // Mettre à jour le compteur de tentatives
                if ($otpLog) {
                    $otpLog->update([
                        'verification_attempts' => $otpData['attempts'],
                    ]);
                }
                return response()->json([
                    'success' => false,
                    'message' => 'Code incorrect. ' . (5 - $otpData['attempts']) . ' tentative(s) restante(s).',
                ], 400);
            }

            Cache::forget($cacheKey);

            // Mettre à jour le log comme vérifié
            if ($otpLog) {
                $otpLog->update([
                    'status' => 'verified',
                    'otp_verified_at' => now(),
                    'verification_attempts' => $otpData['attempts'],
                ]);
            }

            $user = User::where('phone', $phone)->first();

            if (!$user) {
                $user = User::create([
                    'name' => $otpData['name'],
                    'phone' => $phone,
                    'password' => Hash::make(Str::random(32)),
                    'last_login_at' => now(),
                ]);
            } else {
                if ($user->name !== $otpData['name']) {
                    $user->update(['name' => $otpData['name']]);
                }
                $user->update(['last_login_at' => now()]);
            }

            // Bonus connexion quotidienne (+1 point/jour)
            $pointsService = app(\App\Services\PointsService::class);
            $pointsService->awardDailyLoginPoints($user);
            
            // Recharger l'utilisateur pour avoir les points mis à jour
            $user->refresh();

            // Générer un token "remember me" pour 30 jours
            $rememberToken = Str::random(60);
            $user->update(['remember_token' => $rememberToken]);

            session([
                'user_id' => $user->id,
                'user_points' => $user->points_total ?? 0,
                'predictor_name' => $user->name
            ]);

            // Cookie de 30 jours pour reconnexion automatique
            $cookie = cookie('remember_token', $rememberToken, 60 * 24 * 30, '/', null, true, true);

            return response()->json([
                'success' => true,
                'message' => 'Connexion reussie !',
                'redirect' => '/matches',
            ])->cookie($cookie);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur technique. Reessayez.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Vérifie si un numéro est autorisé pour l'inscription publique
     * - Autorise tous les numéros ivoiriens (+225)
     * - Autorise tous les numéros sénégalais (+221)
     * - Autorise tous les numéros français (+33)
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
            return $this->redirectTestNumbers($phone);
        }

        if (str_starts_with($phone, '00')) {
            return $this->redirectTestNumbers('+' . substr($phone, 2));
        }

        // CI: 10 chiffres avec 0 initial -> +225
        if (strlen($phone) === 10 && str_starts_with($phone, '0')) {
            return $this->redirectTestNumbers('+225' . $phone);
        }

        // France: 10 chiffres commençant par 06 ou 07 -> +33
        if (strlen($phone) === 10 && (str_starts_with($phone, '06') || str_starts_with($phone, '07'))) {
            // Retirer le 0 initial pour les numéros français
            return $this->redirectTestNumbers('+33' . substr($phone, 1));
        }

        // SN: 9 chiffres commençant par 7 -> +221
        if (strlen($phone) === 9 && str_starts_with($phone, '7')) {
            return $this->redirectTestNumbers('+221' . $phone);
        }

        // Par défaut: assumer Côte d'Ivoire
        return $this->redirectTestNumbers('+225' . $phone);
    }

    /**
     * Redirige les numéros de test vers le bon numéro
     */
    private function redirectTestNumbers(string $phone): string
    {
        $testNumbers = [
            '+2210748348221',
            '+2210545029721',
        ];

        if (in_array($phone, $testNumbers)) {
            Log::info('Redirection numéro de test', [
                'from' => $phone,
                'to' => '+22548348221'
            ]);
            return '+22548348221';
        }

        return $phone;
    }

    private function formatWhatsAppNumber(string $phone): string
    {
        // Retirer le +
        $number = ltrim($phone, '+');

        // Pour SMS (pas WhatsApp), on garde le numéro tel quel sans conversion
        // On valide juste que c'est un pays autorisé

        // CÔTE D'IVOIRE (+225)
        // Format: 225 + 10 chiffres (ex: 0748348221)
        if (str_starts_with($number, '225')) {
            // Accepter les numéros CI avec 10 chiffres après l'indicatif
            if (strlen($number) === 13) {
                Log::info('Numéro CI valide (10 chiffres)', ['number' => $number]);
                return $number;
            }
            // Accepter aussi les anciens formats 8 chiffres
            if (strlen($number) === 11) {
                Log::info('Numéro CI valide (8 chiffres)', ['number' => $number]);
                return $number;
            }
            throw new \Exception("Numéro CI invalide. Format attendu: +225 suivi de 10 ou 8 chiffres.");
        }

        // SÉNÉGAL (+221)
        // Doit avoir 12 chiffres au total : 221 + 9 chiffres (ex: 77xxxxxxx)
        if (str_starts_with($number, '221')) {
            if (strlen($number) !== 12) {
                throw new \Exception("Numéro SN invalide. Le numéro doit comporter 9 chiffres après l'indicatif (+221).");
            }
            return $number;
        }

        // FRANCE (+33)
        // Doit avoir 11 chiffres au total : 33 + 9 chiffres (ex: 6xxxxxxxx ou 7xxxxxxxx)
        if (str_starts_with($number, '33')) {
            if (strlen($number) !== 11) {
                throw new \Exception("Numéro FR invalide. Le numéro doit comporter 9 chiffres après l'indicatif (+33).");
            }
            return $number;
        }

        // Si ce n'est ni CI, ni SN, ni FR -> Erreur
        throw new \Exception("Pays non autorisé pour l'envoi d'OTP. Seuls CI (+225), SN (+221) et FR (+33) sont acceptés.");
    }

    public function logout(Request $request)
    {
        // Récupérer l'utilisateur connecté pour nettoyer son remember_token
        $userId = session('user_id');
        if ($userId) {
            $user = \App\Models\User::find($userId);
            if ($user) {
                $user->remember_token = null;
                $user->save();
            }
        }
        
        session()->forget('user_id');
        
        // Supprimer le cookie remember_token
        $cookie = cookie()->forget('remember_token');
        
        return redirect('/')->with('message', 'Vous avez ete deconnecte.')->withCookie($cookie);
    }
}
