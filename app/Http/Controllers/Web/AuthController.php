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

            $whatsappNumber = $this->formatWhatsAppNumber($phone);

            Log::info('=== ENVOI OTP WHATSAPP ===', [
                'original_phone' => $originalPhone,
                'formatted_phone' => $phone,
                'whatsapp_number' => $whatsappNumber,
                'name' => $request->name,
            ]);

            $otpCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            $cacheKey = 'otp_' . $whatsappNumber;
            Cache::put($cacheKey, [
                'code' => $otpCode,
                'name' => $request->name,
                'phone' => $phone,
                'attempts' => 0,
            ], now()->addMinutes(10));

            // SÉCURITÉ: Ne jamais logger le code OTP en production
            Log::info('Code OTP genere', ['whatsapp_number' => $whatsappNumber]);

            $result = $this->sendWhatsAppMessage($whatsappNumber, $otpCode);

            // Enregistrer le log OTP
            $otpLog = AdminOtpLog::create([
                'phone' => $phone,
                'code' => $otpCode,
                'whatsapp_number' => $whatsappNumber,
                'status' => $result['success'] ? 'sent' : 'failed',
                'otp_sent_at' => now(),
                'error_message' => $result['success'] ? null : ($result['error'] ?? 'Erreur inconnue'),
            ]);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Code envoye sur WhatsApp !',
                    'whatsapp_number' => $whatsappNumber,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l envoi du message WhatsApp.',
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

    private function sendWhatsAppMessage(string $whatsappNumber, string $otpCode): array
    {
        Log::info('=== DEBUT sendWhatsAppMessage ===');

        $idInstance = config('services.greenapi.id_instance');
        $apiToken = config('services.greenapi.api_token');
        $baseUrl = config('services.greenapi.url');

        Log::info('Configuration Green API', [
            'id_instance' => $idInstance,
            'api_token' => $apiToken ? substr($apiToken, 0, 15) . '...' : 'NULL',
            'base_url' => $baseUrl,
        ]);

        if (!$idInstance || !$apiToken || !$baseUrl) {
            Log::error('Configuration Green API incomplete !', [
                'id_instance_set' => !empty($idInstance),
                'api_token_set' => !empty($apiToken),
                'base_url_set' => !empty($baseUrl),
            ]);
            return ['success' => false, 'error' => 'Configuration Green API incomplete'];
        }

        $url = "{$baseUrl}/waInstance{$idInstance}/sendMessage/{$apiToken}";

        Log::info('URL Green API', ['url' => $url]);

        $message = "⚽ SOBOA FOOT TIME\n\nVotre code de vérification :\n\n👉 ```{$otpCode}``` 👈\n\n_Le jeu commence ici !_";

        $payload = [
            'chatId' => $whatsappNumber . '@c.us',
            'message' => $message,
        ];

        Log::info('Payload WhatsApp', $payload);

        try {
            Log::info('Envoi requete HTTP vers Green API...');

            $response = Http::timeout(30)->post($url, $payload);

            Log::info('Reponse Green API recue', [
                'status' => $response->status(),
                'body' => $response->body(),
                'headers' => $response->headers(),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('=== SUCCES WhatsApp ===', ['data' => $data]);
                return ['success' => true, 'idMessage' => $data['idMessage'] ?? null];
            } else {
                Log::error('=== ECHEC WhatsApp ===', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return ['success' => false, 'error' => 'HTTP ' . $response->status() . ': ' . $response->body()];
            }
        } catch (\Exception $e) {
            Log::error('=== EXCEPTION WhatsApp ===', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
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

            $whatsappNumber = $this->formatWhatsAppNumber($phone);

            $cacheKey = 'otp_' . $whatsappNumber;
            $otpData = Cache::get($cacheKey);

            // Récupérer le log OTP pour mise à jour
            $otpLog = AdminOtpLog::where('whatsapp_number', $whatsappNumber)
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
            Cache::put($cacheKey, $otpData, now()->addMinutes(10));

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

            session([
                'user_id' => $user->id,
                'user_points' => $user->points_total ?? 0,
                'predictor_name' => $user->name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Connexion reussie !',
                'redirect' => '/matches',
            ]);

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

        // Validation et Formatage stricts

        // CÔTE D'IVOIRE (+225)
        // Doit avoir 13 chiffres au total : 225 + 10 chiffres (ex: 07xxxxxxxx)
        if (str_starts_with($number, '225')) {
            if (strlen($number) !== 13) {
                throw new \Exception("Numéro CI invalide. Le numéro doit comporter 10 chiffres après l'indicatif (+225).");
            }
            // Conversion au format 8 chiffres pour la CI (demande specifique)
            // On retire les 2 premiers chiffres du numéro local (index 3 et 4)
            // Ex: 225 07 48 34 82 21 -> 225 48 34 82 21
            // 225 (0,1,2) + local (3...12) -> on garde 225 et on prend à partir de l'index 5 (le 6ème caractère)
            // ATTENTION: substr est 0-indexed.
            // 2 2 5 0 7 4 8 3 4 8 2 2 1
            // 0 1 2 3 4 5 6 7 8 9 0 1 2
            // On veut garder '225' + '48348221' (à partir de l'index 5)

            $prefixCurrent = substr($number, 0, 3); // 225
            $suffix8 = substr($number, 5); // les 8 derniers chiffres

            $formatted = $prefixCurrent . $suffix8;

            Log::info('Conversion CI 8 chiffres', ['original' => $number, 'converted' => $formatted]);

            return $formatted;
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
        session()->forget('user_id');
        return redirect('/')->with('message', 'Vous avez ete deconnecte.');
    }
}
