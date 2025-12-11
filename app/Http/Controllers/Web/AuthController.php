<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;
use Twilio\Exceptions\TwilioException;

class AuthController extends Controller
{
    /**
     * Affiche le formulaire de connexion/inscription
     */
    public function showLoginForm()
    {
        if (session('user_id')) {
            return redirect('/matches');
        }
        return view('auth.login');
    }

    /**
     * Envoie un code OTP via Twilio Verify
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'name' => 'required|string|max:255',
        ]);

        try {
            $phone = $this->formatPhone($request->phone);
            
            // Stocker le nom en session pour l'utiliser après vérification
            session(['pending_name' => $request->name, 'pending_phone' => $phone]);

            $twilio = new Client(
                config('services.twilio.sid'),
                config('services.twilio.token')
            );

            $verification = $twilio->verify->v2
                ->services(config('services.twilio.verify_sid'))
                ->verifications
                ->create($phone, "sms");

            Log::info('OTP envoyé via Twilio', [
                'phone' => $phone,
                'status' => $verification->status,
                'sid' => $verification->sid
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Code envoyé par SMS',
                'phone' => $phone
            ]);

        } catch (TwilioException $e) {
            Log::error('Erreur Twilio: ' . $e->getMessage(), [
                'code' => $e->getCode(),
                'phone' => $request->phone
            ]);

            $errorMessage = 'Erreur lors de l\'envoi du SMS.';
            
            // Messages d'erreur plus spécifiques
            if (str_contains($e->getMessage(), 'Invalid parameter')) {
                $errorMessage = 'Numéro de téléphone invalide.';
            } elseif (str_contains($e->getMessage(), 'unverified')) {
                $errorMessage = 'Ce numéro ne peut pas recevoir de SMS pour le moment.';
            }

            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'error' => $e->getMessage()
            ], 400);

        } catch (\Exception $e) {
            Log::error('Erreur envoi OTP: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi du code. Veuillez réessayer.'
            ], 500);
        }
    }

    /**
     * Vérifie le code OTP et connecte l'utilisateur
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'code' => 'required|string|size:6',
        ]);

        try {
            $phone = $this->formatPhone($request->phone);
            $name = session('pending_name', 'Utilisateur');

            $twilio = new Client(
                config('services.twilio.sid'),
                config('services.twilio.token')
            );

            $verificationCheck = $twilio->verify->v2
                ->services(config('services.twilio.verify_sid'))
                ->verificationChecks
                ->create([
                    'to' => $phone,
                    'code' => $request->code
                ]);

            Log::info('Vérification OTP Twilio', [
                'phone' => $phone,
                'status' => $verificationCheck->status
            ]);

            if ($verificationCheck->status !== 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Code incorrect. Veuillez réessayer.'
                ], 400);
            }

            // Code vérifié, créer ou connecter l'utilisateur
            $user = User::where('phone', $phone)->first();

            if (!$user) {
                // Nouvel utilisateur
                $user = User::create([
                    'name' => $name,
                    'phone' => $phone,
                    'password' => Hash::make(Str::random(16)),
                    'points_total' => 0,
                    'phone_verified' => true,
                ]);
                Log::info('Nouvel utilisateur créé via Twilio', ['user_id' => $user->id, 'phone' => $phone]);
            } else {
                // Mettre à jour l'utilisateur existant
                $user->update([
                    'phone_verified' => true,
                    'last_login_at' => now(),
                ]);
                Log::info('Utilisateur connecté via Twilio', ['user_id' => $user->id, 'phone' => $phone]);
            }

            // Stocker les infos utilisateur en session
            session([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_phone' => $user->phone,
                'predictor_name' => $user->name,
                'user_points' => $user->points_total,
            ]);

            // Nettoyer les données temporaires
            session()->forget(['pending_name', 'pending_phone']);

            return response()->json([
                'success' => true,
                'message' => 'Connexion réussie !',
                'redirect' => '/matches',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                ]
            ]);

        } catch (TwilioException $e) {
            Log::error('Erreur vérification Twilio: ' . $e->getMessage());
            
            $errorMessage = 'Code incorrect ou expiré.';
            if (str_contains($e->getMessage(), 'not found')) {
                $errorMessage = 'Code expiré. Veuillez demander un nouveau code.';
            }

            return response()->json([
                'success' => false,
                'message' => $errorMessage
            ], 400);

        } catch (\Exception $e) {
            Log::error('Erreur vérification OTP: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la vérification. Veuillez réessayer.'
            ], 500);
        }
    }

    /**
     * Déconnexion
     */
    public function logout()
    {
        session()->flush();
        return redirect('/')->with('success', 'Vous avez été déconnecté.');
    }

    /**
     * Formate le numéro de téléphone au format E.164
     */
    private function formatPhone(string $phone): string
    {
        // Supprimer les espaces et caractères spéciaux
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // Si le numéro ne commence pas par +, ajouter +225 (Côte d'Ivoire)
        if (!str_starts_with($phone, '+')) {
            if (str_starts_with($phone, '00')) {
                $phone = '+' . substr($phone, 2);
            } elseif (str_starts_with($phone, '0')) {
                $phone = '+225' . substr($phone, 1);
            } elseif (str_starts_with($phone, '225')) {
                $phone = '+' . $phone;
            } else {
                $phone = '+225' . $phone;
            }
        }
        
        return $phone;
    }
}
