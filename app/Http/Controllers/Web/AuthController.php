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

                // Reconnecter automatiquement (régénérer la session pour éviter la fixation)
                request()->session()->regenerate();
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

        return view('auth.login', ['testMode' => $this->isTestModeEnabled()]);
    }

    public function showRegisterForm()
    {
        // Si déjà connecté via session
        if (session('user_id')) {
            return redirect('/matches');
        }

        return view('auth.register', ['testMode' => $this->isTestModeEnabled()]);
    }

    /**
     * Login classique avec numéro + mot de passe
     * Compatible avec les anciens utilisateurs (code OTP stocké dans otp_password)
     */
    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        try {
            $phone = $this->formatPhone($request->phone);

            // Validation du pays autorisé (SN, ou CI si le mode test admin est actif)
            if ($err = $this->phoneValidationError($phone)) {
                return response()->json(['success' => false, 'message' => $err[0]], $err[1]);
            }

            // Trouver l'utilisateur
            $user = User::where('phone', $phone)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun compte trouvé avec ce numéro. Veuillez vous inscrire.',
                ], 404);
            }

            // Vérifier le mot de passe
            // Anciens utilisateurs: vérifier contre otp_password
            // Nouveaux utilisateurs: vérifier contre password
            $isValidPassword = false;

            if ($user->password && Hash::check($request->password, $user->password)) {
                // Nouveau système: mot de passe principal
                $isValidPassword = true;
            } elseif ($user->otp_password && Hash::check($request->password, $user->otp_password)) {
                // Ancien système: code OTP comme mot de passe
                $isValidPassword = true;
            }

            if (!$isValidPassword) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mot de passe incorrect.',
                ], 401);
            }

            // Connexion réussie
            Log::info('Connexion réussie', ['phone' => $phone, 'user_id' => $user->id]);

            $user->update(['last_login_at' => now()]);

            // Bonus connexion quotidienne (+1 point/jour)
            $pointsService = app(\App\Services\PointsService::class);
            $pointsService->awardDailyLoginPoints($user);

            $user->refresh();

            // Générer un token "remember me" longue durée (6 mois)
            $rememberToken = Str::random(60);
            $user->update(['remember_token' => $rememberToken]);

            // Régénérer la session pour éviter la fixation de session
            $request->session()->regenerate();

            session([
                'user_id' => $user->id,
                'user_points' => $user->points_total ?? 0,
                'predictor_name' => $user->name
            ]);

            // Cookie remember longue durée
            $rememberMinutes = 60 * 24 * 180; // 6 mois glissants : durée toujours positive, peu importe la date
            // secure = true seulement en production (HTTPS), false en local (HTTP)
            $isSecure = app()->environment('production') || request()->isSecure();
            $cookie = cookie('remember_token', $rememberToken, $rememberMinutes, '/', null, $isSecure, true, false, 'Lax');

            return response()->json([
                'success' => true,
                'message' => 'Connexion réussie !',
                'redirect' => '/matches',
            ])->cookie($cookie);

        } catch (\Exception $e) {
            Log::error('Exception login', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur technique. Réessayez.',
            ], 500);
        }
    }

    /**
     * Inscription : le mot de passe est généré automatiquement,
     * envoyé une seule fois par SMS et consultable dans l'espace personnel.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string',
        ]);

        try {
            $phone = $this->formatPhone($request->phone);

            // Validation du pays autorisé (SN, ou CI si le mode test admin est actif)
            if ($err = $this->phoneValidationError($phone)) {
                return response()->json(['success' => false, 'message' => $err[0]], $err[1]);
            }

            // Vérifier si l'utilisateur existe déjà
            $existingUser = User::where('phone', $phone)->first();

            if ($existingUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce numéro est déjà enregistré. Veuillez vous connecter.',
                ], 409);
            }

            // Générer automatiquement le mot de passe (lisible, sans caractères ambigus)
            $generatedPassword = $this->generateReadablePassword();

            // Créer le nouvel utilisateur :
            // - password : hash bcrypt pour l'authentification
            // - password_encrypted : chiffrement réversible pour l'affichage espace perso
            $user = User::create([
                'name' => $request->name,
                'phone' => $phone,
                'password' => Hash::make($generatedPassword),
                'last_login_at' => now(),
            ]);
            $user->setPlainPassword($generatedPassword);
            $user->save();

            Log::info('Nouveau compte créé avec mot de passe généré', ['phone' => $phone, 'user_id' => $user->id]);

            // Envoyer le mot de passe par SMS UNE SEULE FOIS (à la création)
            $smsResult = $this->sendPasswordResetSMS($phone, $generatedPassword, $user->name);
            if (!$smsResult['success']) {
                Log::warning('Échec envoi SMS mot de passe à l\'inscription', [
                    'phone' => $phone,
                    'error' => $smsResult['error'] ?? 'unknown',
                ]);
            }

            // Bonus connexion quotidienne (+1 point/jour)
            $pointsService = app(\App\Services\PointsService::class);
            $pointsService->awardDailyLoginPoints($user);

            $user->refresh();

            // Régénérer la session pour éviter la fixation de session
            $request->session()->regenerate();

            // Générer un token "remember me" longue durée (6 mois)
            $rememberToken = Str::random(60);
            $user->update(['remember_token' => $rememberToken]);

            session([
                'user_id' => $user->id,
                'user_points' => $user->points_total ?? 0,
                'predictor_name' => $user->name
            ]);

            // Cookie remember longue durée
            $rememberMinutes = 60 * 24 * 180; // 6 mois glissants : durée toujours positive, peu importe la date
            $isSecure = app()->environment('production') || request()->isSecure();
            $cookie = cookie('remember_token', $rememberToken, $rememberMinutes, '/', null, $isSecure, true, false, 'Lax');

            return response()->json([
                'success' => true,
                'message' => 'Compte créé ! Votre mot de passe vous a été envoyé par SMS et reste visible dans votre espace personnel.',
                'redirect' => '/mes-pronostics',
            ])->cookie($cookie);

        } catch (\Exception $e) {
            Log::error('Exception register', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur technique. Réessayez.',
            ], 500);
        }
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

            // VALIDATION STRICTE: Seuls les numéros sénégalais et ivoiriens sont autorisés
            if (!str_starts_with($phone, '+221') && !str_starts_with($phone, '+225')) {
                Log::warning('Tentative d\'inscription avec un numéro non autorisé', [
                    'phone' => $phone,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Seuls les numéros sénégalais (+221) et ivoiriens (+225) sont autorisés.',
                ], 403);
            }

            // VALIDATION FORMAT selon le pays
            if (str_starts_with($phone, '+221')) {
                // SÉNÉGAL: +221 + 9 chiffres (commençant par 7)
                $phoneWithoutPrefix = substr($phone, 4);
                if (strlen($phoneWithoutPrefix) !== 9 || !str_starts_with($phoneWithoutPrefix, '7')) {
                    Log::warning('Format numéro sénégalais invalide', [
                        'phone' => $phone,
                        'phone_without_prefix' => $phoneWithoutPrefix,
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Format de numéro invalide. Le numéro sénégalais doit contenir 9 chiffres commençant par 7 (ex: 77 XXX XX XX).',
                    ], 400);
                }
            } elseif (str_starts_with($phone, '+225')) {
                // CÔTE D'IVOIRE: +225 + 10 chiffres (commençant par 0)
                $phoneWithoutPrefix = substr($phone, 4);
                if (strlen($phoneWithoutPrefix) !== 10 || !str_starts_with($phoneWithoutPrefix, '0')) {
                    Log::warning('Format numéro ivoirien invalide', [
                        'phone' => $phone,
                        'phone_without_prefix' => $phoneWithoutPrefix,
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Format de numéro invalide. Le numéro ivoirien doit contenir 10 chiffres commençant par 0 (ex: 07 XX XX XX XX).',
                    ], 400);
                }
            }

            // Vérifier si l'utilisateur existe déjà (a déjà un mot de passe)
            $existingUser = User::where('phone', $phone)->first();
            
            if ($existingUser && $existingUser->otp_password) {
                // L'utilisateur existe et a déjà un code permanent
                // Pas besoin d'envoyer de SMS, il peut se connecter avec son code
                Log::info('Utilisateur existant avec code permanent', ['phone' => $phone]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Vous avez déjà un compte. Entrez votre code personnel.',
                    'phone' => $phone,
                    'has_password' => true,
                ]);
            }

            // RATE LIMITING: 1 OTP par heure par numéro (seulement pour les nouveaux)
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

            Log::info('=== ENVOI OTP SMS (Nouveau compte) ===', [
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
                'is_new_user' => true,
            ], now()->addHour());

            // SÉCURITÉ: Ne jamais logger le code OTP en production
            Log::info('Code OTP genere pour nouveau compte', ['phone' => $phone]);

            // Message SMS indiquant que c'est le code permanent
            $result = $this->sendSMS($phone, $otpCode, true);

            // Enregistrer le rate limit (1 heure)
            if ($result['success']) {
                Cache::put($rateLimitKey, now(), now()->addHour());
            }

            // Enregistrer le log OTP
            $otpLog = AdminOtpLog::create([
                'phone' => $phone,
                'code' => $otpCode,
                'whatsapp_number' => $phone,
                'status' => $result['success'] ? 'sent' : 'failed',
                'otp_sent_at' => now(),
                'error_message' => $result['success'] ? null : ($result['error'] ?? 'Erreur inconnue'),
            ]);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Code envoyé par SMS ! Ce code sera votre mot de passe permanent.',
                    'phone' => $phone,
                    'has_password' => false,
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
    private function sendSMS(string $phone, string $otpCode, bool $isPermanent = false): array
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

        // Message différent si c'est un code permanent
        if ($isPermanent) {
            $message = "SOBOA FOOT TIME - Votre code personnel: {$otpCode}. IMPORTANT: Conservez ce code, il sera votre mot de passe pour toutes vos connexions futures.";
        } else {
            $message = "SOBOA FOOT TIME - Votre code de verification: {$otpCode}";
        }

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

            // CAS 1: Utilisateur existant avec mot de passe permanent
            $existingUser = User::where('phone', $phone)->first();
            
            if ($existingUser && $existingUser->otp_password) {
                // Vérifier le mot de passe permanent (hashé)
                if (!Hash::check($request->code, $existingUser->otp_password)) {
                    Log::warning('Tentative de connexion avec mauvais code permanent', ['phone' => $phone]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Code incorrect. Utilisez le code reçu lors de votre première inscription.',
                    ], 400);
                }

                // Connexion réussie avec code permanent
                Log::info('Connexion avec code permanent réussie', ['phone' => $phone, 'user_id' => $existingUser->id]);
                
                // Mettre à jour le nom si différent
                $name = $request->input('name', $existingUser->name);
                if ($name && $existingUser->name !== $name) {
                    $existingUser->update(['name' => $name]);
                }
                $existingUser->update(['last_login_at' => now()]);

                // Bonus connexion quotidienne (+1 point/jour)
                $pointsService = app(\App\Services\PointsService::class);
                $pointsService->awardDailyLoginPoints($existingUser);
                
                $existingUser->refresh();

                // Générer un token "remember me" 
                $rememberToken = Str::random(60);
                $existingUser->update(['remember_token' => $rememberToken]);

                request()->session()->regenerate();
                session([
                    'user_id' => $existingUser->id,
                    'user_points' => $existingUser->points_total ?? 0,
                    'predictor_name' => $existingUser->name
                ]);

                $rememberMinutes = 60 * 24 * 180; // 6 mois glissants : durée toujours positive, peu importe la date
                $isSecure = app()->environment('production') || request()->isSecure();
                $cookie = cookie('remember_token', $rememberToken, $rememberMinutes, '/', null, $isSecure, true, false, 'Lax');

                return response()->json([
                    'success' => true,
                    'message' => 'Connexion réussie !',
                    'redirect' => '/matches',
                ])->cookie($cookie);
            }

            // CAS 2: Nouveau compte - Vérification OTP classique via cache
            $cacheKey = 'otp_' . $phone;
            $otpData = Cache::get($cacheKey);

            // Récupérer le log OTP pour mise à jour
            $otpLog = AdminOtpLog::where('phone', $phone)
                ->where('status', 'sent')
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$otpData) {
                // Peut-être que l'utilisateur a déjà un compte mais otp_password est null (ancien compte)
                if ($existingUser) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Veuillez demander un nouveau code pour activer votre compte.',
                    ], 400);
                }
                
                if ($otpLog) {
                    $otpLog->update([
                        'status' => 'expired',
                        'verification_attempts' => ($otpLog->verification_attempts ?? 0) + 1,
                    ]);
                }
                return response()->json([
                    'success' => false,
                    'message' => 'Code expiré. Veuillez demander un nouveau code.',
                ], 400);
            }

            if ($otpData['attempts'] >= 5) {
                Cache::forget($cacheKey);
                if ($otpLog) {
                    $otpLog->update([
                        'status' => 'failed',
                        'verification_attempts' => 5,
                        'error_message' => 'Trop de tentatives',
                    ]);
                }
                return response()->json([
                    'success' => false,
                    'message' => 'Trop de tentatives. Veuillez demander un nouveau code.',
                ], 400);
            }

            $otpData['attempts']++;
            Cache::put($cacheKey, $otpData, now()->addHour());

            if ($otpData['code'] !== $request->code) {
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

            // Créer ou mettre à jour l'utilisateur avec le code comme mot de passe permanent
            $user = User::where('phone', $phone)->first();

            if (!$user) {
                $user = User::create([
                    'name' => $otpData['name'],
                    'phone' => $phone,
                    'password' => Hash::make(Str::random(32)),
                    'otp_password' => Hash::make($request->code), // Code OTP devient mot de passe permanent
                    'last_login_at' => now(),
                ]);
                Log::info('Nouveau compte créé avec code permanent', ['phone' => $phone, 'user_id' => $user->id]);
            } else {
                // Mettre à jour le compte existant avec le nouveau code permanent
                $user->update([
                    'name' => $otpData['name'],
                    'otp_password' => Hash::make($request->code),
                    'last_login_at' => now(),
                ]);
                Log::info('Compte mis à jour avec nouveau code permanent', ['phone' => $phone, 'user_id' => $user->id]);
            }

            // Bonus connexion quotidienne (+1 point/jour)
            $pointsService = app(\App\Services\PointsService::class);
            $pointsService->awardDailyLoginPoints($user);
            
            $user->refresh();

            // Générer un token "remember me" 
            $rememberToken = Str::random(60);
            $user->update(['remember_token' => $rememberToken]);

            request()->session()->regenerate();
            session([
                'user_id' => $user->id,
                'user_points' => $user->points_total ?? 0,
                'predictor_name' => $user->name
            ]);

            $rememberMinutes = 60 * 24 * 180; // 6 mois glissants : durée toujours positive, peu importe la date
            $isSecure = app()->environment('production') || request()->isSecure();
            $cookie = cookie('remember_token', $rememberToken, $rememberMinutes, '/', null, $isSecure, true, false, 'Lax');

            return response()->json([
                'success' => true,
                'message' => 'Connexion réussie ! Conservez votre code, il sera votre mot de passe pour les prochaines connexions.',
                'redirect' => '/matches',
            ])->cookie($cookie);

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
     * - Autorise tous les numéros ivoiriens (+225)
     * - Autorise tous les numéros sénégalais (+221)
     * - Autorise tous les numéros français (+33)
     */
    /**
     * Indique si le mode test admin est actif (autorise les numéros de Côte d'Ivoire).
     */
    private function isTestModeEnabled(): bool
    {
        return (bool) optional(\App\Models\SiteSetting::first())->test_mode;
    }

    /**
     * Valide le pays/format d'un numéro déjà au format E.164.
     * - Mode test actif : autorise la Côte d'Ivoire (+225 + 10 chiffres commençant par 0).
     * - Sinon : Sénégal uniquement (+221 + 9 chiffres commençant par 7).
     *
     * @return array{0:string,1:int}|null  [message, httpStatus] si erreur, sinon null.
     */
    private function phoneValidationError(string $phone): ?array
    {
        if ($this->isTestModeEnabled() && str_starts_with($phone, '+225')) {
            $ci = substr($phone, 4);
            if (strlen($ci) !== 10 || !str_starts_with($ci, '0')) {
                return ['Format ivoirien invalide (10 chiffres commençant par 0).', 400];
            }
            return null;
        }

        if (!str_starts_with($phone, '+221')) {
            return ['Seuls les numéros sénégalais (+221) sont autorisés.', 403];
        }

        $sn = substr($phone, 4);
        if (strlen($sn) !== 9 || !str_starts_with($sn, '7')) {
            return ['Format de numéro invalide. Le numéro doit contenir 9 chiffres commençant par 7.', 400];
        }

        return null;
    }

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
     * Note: Cette fonction n'est plus utilisée pour les redirections spéciales
     */
    private function redirectTestNumbers(string $phone): string
    {
        // Plus de redirections spéciales - retourner le numéro tel quel
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

    /**
     * Demander un nouveau code (pour les utilisateurs qui ont oublié leur code permanent)
     * Cette méthode force l'envoi d'un nouveau SMS même si l'utilisateur a déjà un code
     */
    public function requestNewCode(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'name' => 'required|string|max:255',
        ]);

        try {
            $phone = $this->formatPhone($request->phone);

            // VALIDATION STRICTE: Seuls les numéros sénégalais et ivoiriens sont autorisés
            if (!str_starts_with($phone, '+221') && !str_starts_with($phone, '+225')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Seuls les numéros sénégalais (+221) et ivoiriens (+225) sont autorisés.',
                ], 403);
            }

            // VALIDATION FORMAT selon le pays
            if (str_starts_with($phone, '+221')) {
                $phoneWithoutPrefix = substr($phone, 4);
                if (strlen($phoneWithoutPrefix) !== 9 || !str_starts_with($phoneWithoutPrefix, '7')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Format de numéro invalide.',
                    ], 400);
                }
            } elseif (str_starts_with($phone, '+225')) {
                $phoneWithoutPrefix = substr($phone, 4);
                if (strlen($phoneWithoutPrefix) !== 10 || !str_starts_with($phoneWithoutPrefix, '0')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Format de numéro invalide.',
                    ], 400);
                }
            }

            // Vérifier que l'utilisateur existe
            $user = User::where('phone', $phone)->first();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun compte trouvé avec ce numéro. Veuillez vous inscrire.',
                ], 404);
            }

            // RATE LIMITING: 1 demande de nouveau code par heure
            $rateLimitKey = 'new_code_rate_limit_' . md5($phone);
            $lastRequest = Cache::get($rateLimitKey);
            
            if ($lastRequest) {
                $minutesRemaining = now()->diffInMinutes($lastRequest->addHour(), false);
                
                if ($minutesRemaining > 0) {
                    return response()->json([
                        'success' => false,
                        'message' => "Vous avez déjà demandé un nouveau code. Veuillez attendre {$minutesRemaining} minute(s).",
                        'rate_limited' => true,
                    ], 429);
                }
            }

            Log::info('=== DEMANDE NOUVEAU CODE (reset password) ===', [
                'phone' => $phone,
                'user_id' => $user->id,
            ]);

            // Générer un nouveau code
            $otpCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            // Stocker temporairement dans le cache
            $cacheKey = 'otp_' . $phone;
            Cache::put($cacheKey, [
                'code' => $otpCode,
                'name' => $request->name,
                'phone' => $phone,
                'attempts' => 0,
                'is_reset' => true, // Marquer comme reset de code
            ], now()->addHour());

            // Envoyer le SMS avec message spécial
            $result = $this->sendSMS($phone, $otpCode, true);

            if ($result['success']) {
                Cache::put($rateLimitKey, now(), now()->addHour());
                
                // Réinitialiser le otp_password pour forcer la vérification par OTP
                $user->update(['otp_password' => null]);

                // Log pour admin
                AdminOtpLog::create([
                    'phone' => $phone,
                    'code' => $otpCode,
                    'whatsapp_number' => $phone,
                    'status' => 'sent',
                    'otp_sent_at' => now(),
                    'error_message' => 'Reset password request',
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Un nouveau code vous a été envoyé par SMS.',
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
            Log::error('Exception requestNewCode', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur technique. Réessayez.',
            ], 500);
        }
    }

    /**
     * Afficher le formulaire de récupération de mot de passe
     */
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Réinitialiser le mot de passe et l'envoyer par SMS
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        try {
            $phone = $this->formatPhone($request->phone);

            // Vérifier que le numéro est autorisé (format sénégalais)
            if (!str_starts_with($phone, '+221')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Seuls les numéros sénégalais (+221) sont autorisés.',
                ], 403);
            }

            // VALIDATION FORMAT sénégalais
            $phoneWithoutPrefix = substr($phone, 4);
            if (strlen($phoneWithoutPrefix) !== 9 || !str_starts_with($phoneWithoutPrefix, '7')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format de numéro invalide.',
                ], 400);
            }

            // Trouver l'utilisateur
            $user = User::where('phone', $phone)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun compte trouvé avec ce numéro.',
                ], 404);
            }

            // Générer un nouveau mot de passe aléatoire (8 caractères, facile à lire)
            $newPassword = $this->generateReadablePassword();

            // Mettre à jour le mot de passe (hash + version chiffrée pour l'espace perso)
            $user->update([
                'password' => Hash::make($newPassword),
            ]);
            $user->setPlainPassword($newPassword);
            $user->save();

            // Envoyer le nouveau mot de passe par SMS
            $smsResult = $this->sendPasswordResetSMS($phone, $newPassword, $user->name);

            if (!$smsResult['success']) {
                Log::error('Échec envoi SMS mot de passe', ['phone' => $phone, 'error' => $smsResult['error'] ?? 'unknown']);
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l\'envoi du SMS. Veuillez réessayer.',
                ], 500);
            }

            Log::info('Mot de passe réinitialisé et envoyé par SMS', ['phone' => $phone, 'user_id' => $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'Votre nouveau mot de passe a été envoyé par SMS au ' . $this->maskPhone($phone),
                'user_name' => $user->name,
            ]);

        } catch (\Exception $e) {
            Log::error('Exception resetPassword', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur technique. Réessayez.',
            ], 500);
        }
    }

    /**
     * Envoie un SMS avec le nouveau mot de passe
     */
    private function sendPasswordResetSMS(string $phone, string $password, string $userName): array
    {
        Log::info('=== DEBUT sendPasswordResetSMS (Twilio) ===');

        $accountSid = config('services.twilio.account_sid');
        $authToken = config('services.twilio.auth_token');
        $fromNumber = config('services.twilio.from_number');

        if (!$accountSid || !$authToken || !$fromNumber) {
            Log::error('Configuration Twilio incomplete !');
            return ['success' => false, 'error' => 'Configuration Twilio incomplete'];
        }

        // Formater le numéro au format international avec +
        $toNumber = '+' . ltrim($phone, '+');

        $message = "SOBOA FOOT TIME - Bonjour {$userName}! Votre nouveau mot de passe est: {$password}. Conservez-le précieusement pour vos connexions futures.";

        try {
            Log::info('Envoi SMS mot de passe via Twilio...', [
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

            if ($response->successful()) {
                $data = $response->json();
                Log::info('=== SUCCES SMS mot de passe Twilio ===', ['sid' => $data['sid'] ?? null]);
                return ['success' => true, 'sid' => $data['sid'] ?? null];
            } else {
                $errorData = $response->json();
                $errorMessage = $errorData['message'] ?? ('HTTP ' . $response->status());
                Log::error('=== ECHEC SMS mot de passe Twilio ===', [
                    'status' => $response->status(),
                    'error' => $errorMessage,
                ]);
                return ['success' => false, 'error' => $errorMessage];
            }
        } catch (\Exception $e) {
            Log::error('=== EXCEPTION Twilio mot de passe ===', [
                'message' => $e->getMessage(),
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Masquer partiellement un numéro de téléphone
     */
    private function maskPhone(string $phone): string
    {
        // +221771234567 -> +221 77 *** ** 67
        if (strlen($phone) >= 10) {
            return substr($phone, 0, 7) . ' ** ** ' . substr($phone, -2);
        }
        return $phone;
    }

    /**
     * Générer un mot de passe facile à lire (sans caractères ambigus)
     */
    private function generateReadablePassword($length = 8)
    {
        // Caractères non ambigus (pas de 0/O, 1/l/I, etc.)
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $password = '';
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        
        return $password;
    }
}
