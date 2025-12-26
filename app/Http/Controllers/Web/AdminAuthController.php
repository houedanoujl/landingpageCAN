<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
     * Authentifie l'admin avec username et mot de passe
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $username = $request->input('username');
        $password = $request->input('password');

        // Récupérer les identifiants depuis .env
        $adminUsername = env('ADMIN_USERNAME');
        $adminPassword = env('ADMIN_PASSWORD');

        Log::info('Tentative de connexion admin', ['username' => $username]);

        // Vérification des identifiants
        if ($username !== $adminUsername || $password !== $adminPassword) {
            Log::warning('Échec connexion admin - identifiants incorrects', ['username' => $username]);
            return back()->withErrors(['credentials' => 'Identifiants incorrects.'])->withInput();
        }

        // Créer ou récupérer l'utilisateur admin
        $admin = User::where('role', 'admin')->first();

        if (!$admin) {
            $admin = User::create([
                'name' => 'Administrateur',
                'phone' => '+2250000000000',
                'role' => 'admin',
                'is_admin' => true,
            ]);
            Log::info('Utilisateur admin créé', ['id' => $admin->id]);
        }

        // Connexion
        session(['user_id' => $admin->id]);

        Log::info('Connexion admin réussie', ['user_id' => $admin->id]);

        return redirect()->route('admin.dashboard')->with('success', 'Bienvenue, Administrateur !');
    }

    /**
     * Déconnexion admin
     */
    public function logout()
    {
        session()->forget('user_id');
        return redirect('/')->with('success', 'Déconnexion réussie.');
    }
}
