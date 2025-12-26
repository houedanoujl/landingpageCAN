<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AdminAuthController extends Controller
{
    /**
     * Identifiants admin (hardcodés)
     */
    private const ADMIN_USERNAME = 'admin';
    private const ADMIN_PASSWORD = 'karniella';

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
     * Connexion admin avec nom d'utilisateur et mot de passe
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        try {
            $username = $request->username;
            $password = $request->password;

            Log::info('Tentative de connexion admin', ['username' => $username]);

            // Vérification des identifiants
            if ($username !== self::ADMIN_USERNAME || $password !== self::ADMIN_PASSWORD) {
                Log::warning('Échec connexion admin - identifiants incorrects', ['username' => $username]);
                return response()->json([
                    'success' => false,
                    'message' => 'Identifiants incorrects.',
                ], 401);
            }

            // Chercher ou créer l'utilisateur admin
            $user = User::where('role', 'admin')->first();

            if (!$user) {
                $user = User::create([
                    'name' => 'Administrateur',
                    'phone' => '+22500000000',
                    'password' => Hash::make(Str::random(32)),
                    'role' => 'admin',
                ]);
                Log::info('Utilisateur admin créé', ['user_id' => $user->id]);
            }

            session(['user_id' => $user->id]);

            Log::info('Connexion admin réussie', ['user_id' => $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'Connexion admin réussie !',
                'redirect' => '/admin',
            ]);

        } catch (\Exception $e) {
            Log::error('Exception connexion admin', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur technique. Réessayez.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Anciennes méthodes OTP (non utilisées mais conservées pour compatibilité)
     */
    public function sendOtp(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'L\'authentification OTP n\'est plus disponible pour l\'administration. Utilisez le formulaire de connexion avec mot de passe.',
        ], 400);
    }

    public function verifyOtp(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'L\'authentification OTP n\'est plus disponible pour l\'administration. Utilisez le formulaire de connexion avec mot de passe.',
        ], 400);
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
