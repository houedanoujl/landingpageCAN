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

class AdminAuthController extends Controller
{
    /**
     * Affiche le formulaire de connexion admin
     */
    public function showLoginForm()
    {
        if (session('user_id')) {
            $user = User::find(session('user_id'));
            if ($user && $user->role === 'admin') {
                return redirect('/admin');
            }
        }
        return view('admin.auth.login');
    }

    /**
     * Envoie l'OTP pour l'admin
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        try {
            $originalPhone = $request->phone;
            $phone = $this->formatPhone($request->phone);

            // VALIDATION STRICTE: Vérifier que le numéro est dans la liste des admins autorisés
            $adminPhones = config('auth_phones.admin_phones', []);

            if (!in_array($phone, $adminPhones)) {
                Log::warning('Tentative de connexion admin avec un numéro non autorisé', [
                    'phone_attempt' => $phone,
                    'admin_phones' => $adminPhones,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé. Ce numéro n\'a pas les droits d\'administrateur.',
                ], 403);
            }

            $whatsappNumber = $this->formatWhatsAppNumber($phone);

            Log::info('=== ENVOI OTP ADMIN ===', [
                'original_phone' => $originalPhone,
                'formatted_phone' => $phone,
                'whatsapp_number' => $whatsappNumber,
            ]);

            $otpCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            $cacheKey = 'admin_otp_' . $whatsappNumber;
            Cache::put($cacheKey, [
                'code' => $otpCode,
                'phone' => $phone,
                'attempts' => 0,
            ], now()->addMinutes(10));

            Log::info('Code OTP admin généré', ['whatsapp_number' => $whatsappNumber, 'code' => $otpCode]);

            $result = $this->sendWhatsAppMessage($whatsappNumber, $otpCode, true);

            if ($result['success']) {
                // Créer un log pour le code OTP envoyé
                try {
                    AdminOtpLog::create([
                        'phone' => $phone,
                        'code' => $otpCode,
                        'status' => 'sent',
                        'whatsapp_number' => $whatsappNumber,
                        'verification_attempts' => 0,
                        'otp_sent_at' => now(),
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Erreur lors de la création du log OTP: ' . $e->getMessage());
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Code administrateur envoyé sur WhatsApp !',
                    'whatsapp_number' => $whatsappNumber,
                ]);
            } else {
                // Créer un log pour l'échec d'envoi
                try {
                    AdminOtpLog::create([
                        'phone' => $phone,
                        'code' => $otpCode,
                        'status' => 'failed',
                        'whatsapp_number' => $whatsappNumber,
                        'verification_attempts' => 0,
                        'otp_sent_at' => now(),
                        'error_message' => $result['error'] ?? 'Erreur inconnue',
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Erreur lors de la création du log OTP (failed): ' . $e->getMessage());
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l\'envoi du message WhatsApp.',
                    'error' => $result['error'] ?? 'Erreur inconnue',
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Exception sendOtp admin', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur technique. Réessayez.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Envoie un message WhatsApp via Green API
     */
    private function sendWhatsAppMessage(string $whatsappNumber, string $otpCode, bool $isAdmin = false): array
    {
        Log::info('=== DEBUT sendWhatsAppMessage ADMIN ===');

        $idInstance = config('services.greenapi.id_instance');
        $apiToken = config('services.greenapi.api_token');
        $baseUrl = config('services.greenapi.url');

        if (!$idInstance || !$apiToken || !$baseUrl) {
            Log::error('Configuration Green API incomplète !');
            return ['success' => false, 'error' => 'Configuration Green API incomplète'];
        }

        $url = "{$baseUrl}/waInstance{$idInstance}/sendMessage/{$apiToken}";

        $message = $isAdmin 
            ? "🔐 Grande Fête du Foot Africain - SOBOA ADMIN\n\n⚡ Code d'accès administrateur :\n\n👉 ```{$otpCode}``` 👈\n\n_(Appuyez sur le code pour le copier)_"
            : "🏆 Grande Fête du Foot Africain - SOBOA\n\nVotre code de vérification privilège :\n\n👉 ```{$otpCode}``` 👈\n\n_(Appuyez sur le code pour le copier)_";

        $payload = [
            'chatId' => $whatsappNumber . '@c.us',
            'message' => $message,
        ];

        Log::info('Payload WhatsApp Admin', $payload);

        try {
            $response = Http::timeout(30)->post($url, $payload);

            Log::info('Réponse Green API Admin reçue', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('=== SUCCÈS WhatsApp Admin ===', ['data' => $data]);
                return ['success' => true, 'idMessage' => $data['idMessage'] ?? null];
            } else {
                Log::error('=== ÉCHEC WhatsApp Admin ===', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return ['success' => false, 'error' => 'HTTP ' . $response->status()];
            }
        } catch (\Exception $e) {
            Log::error('=== EXCEPTION WhatsApp Admin ===', [
                'message' => $e->getMessage(),
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Vérifie l'OTP admin
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'code' => 'required|string|size:6',
        ]);

        try {
            $phone = $this->formatPhone($request->phone);

            // Double vérification: le numéro doit être dans la liste des admins autorisés
            $adminPhones = config('auth_phones.admin_phones', []);
            if (!in_array($phone, $adminPhones)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé.',
                ], 403);
            }

            $whatsappNumber = $this->formatWhatsAppNumber($phone);
            $cacheKey = 'admin_otp_' . $whatsappNumber;
            $otpData = Cache::get($cacheKey);

            if (!$otpData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Code expiré. Veuillez renvoyer un nouveau code.',
                ], 400);
            }

            if ($otpData['attempts'] >= 5) {
                Cache::forget($cacheKey);
                return response()->json([
                    'success' => false,
                    'message' => 'Trop de tentatives. Veuillez renvoyer un nouveau code.',
                ], 400);
            }

            $otpData['attempts']++;
            Cache::put($cacheKey, $otpData, now()->addMinutes(10));

            if ($otpData['code'] !== $request->code) {
                // Incrémenter les tentatives échouées dans le log
                try {
                    AdminOtpLog::where('code', $otpData['code'])
                        ->where('phone', $phone)
                        ->where('status', 'sent')
                        ->increment('verification_attempts');
                } catch (\Exception $e) {
                    Log::warning('Erreur lors de la mise à jour du log OTP: ' . $e->getMessage());
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Code incorrect. ' . (5 - $otpData['attempts']) . ' tentative(s) restante(s).',
                ], 400);
            }

            Cache::forget($cacheKey);

            // Chercher ou créer l'utilisateur admin
            $user = User::where('phone', $phone)->first();

            if (!$user) {
                $user = User::create([
                    'name' => 'Admin ' . substr($phone, -4),
                    'phone' => $phone,
                    'password' => Hash::make(Str::random(32)),
                    'role' => 'admin',
                ]);
            } else {
                // S'assurer que l'utilisateur a le rôle admin
                if ($user->role !== 'admin') {
                    $user->update(['role' => 'admin']);
                }
            }

            // Mettre à jour le log OTP comme vérifié
            try {
                AdminOtpLog::where('code', $otpData['code'])
                    ->where('phone', $phone)
                    ->where('status', 'sent')
                    ->update([
                        'status' => 'verified',
                        'otp_verified_at' => now(),
                    ]);
            } catch (\Exception $e) {
                Log::warning('Erreur lors de la mise à jour du log OTP (verified): ' . $e->getMessage());
            }

            session(['user_id' => $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'Connexion admin réussie !',
                'redirect' => '/admin',
            ]);

        } catch (\Exception $e) {
            Log::error('Exception verifyOtp admin', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur technique. Réessayez.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Formate le numéro de téléphone
     */
    private function formatPhone(string $phone): string
    {
        $phone = preg_replace('/[^\d+]/', '', $phone);

        if (str_starts_with($phone, '+')) {
            return $phone;
        }

        if (str_starts_with($phone, '00')) {
            return '+' . substr($phone, 2);
        }

        // Pour admin CI: 10 chiffres -> +225
        if (strlen($phone) === 10 && str_starts_with($phone, '0')) {
            return '+225' . $phone;
        }

        return '+225' . $phone;
    }

    /**
     * Formate le numéro pour WhatsApp (format 8 chiffres pour CI)
     */
    private function formatWhatsAppNumber(string $phone): string
    {
        $number = ltrim($phone, '+');

        // CÔTE D'IVOIRE (+225) - Format 8 chiffres
        if (str_starts_with($number, '225')) {
            if (strlen($number) !== 13) {
                throw new \Exception("Numéro CI invalide. Le numéro doit comporter 10 chiffres après l'indicatif (+225).");
            }
            
            $prefixCurrent = substr($number, 0, 3); // 225
            $suffix8 = substr($number, 5); // les 8 derniers chiffres
            $formatted = $prefixCurrent . $suffix8;

            Log::info('Conversion CI 8 chiffres', ['original' => $number, 'converted' => $formatted]);
            return $formatted;
        }

        throw new \Exception("Format de numéro non supporté pour l'administration.");
    }

    /**
     * Déconnexion admin
     */
    public function logout(Request $request)
    {
        session()->forget('user_id');
        return redirect('/admin/login')->with('message', 'Vous avez été déconnecté de l\'administration.');
    }
}
