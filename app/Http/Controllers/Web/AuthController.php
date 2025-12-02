<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
     * Callback après authentification Firebase
     * Reçoit le token Firebase et crée/connecte l'utilisateur
     */
    public function firebaseCallback(Request $request)
    {
        $request->validate([
            'firebase_token' => 'required|string',
            'phone' => 'required|string',
            'name' => 'required|string|max:255',
            'firebase_uid' => 'required|string',
        ]);

        try {
            // Vérifier le token Firebase (optionnel mais recommandé en production)
            $verified = $this->verifyFirebaseToken($request->firebase_token);
            
            if (!$verified) {
                Log::warning('Firebase token verification skipped or failed', [
                    'phone' => $request->phone
                ]);
            }

            $phone = $this->formatPhone($request->phone);
            
            // Trouver ou créer l'utilisateur
            $user = User::where('phone', $phone)->first();
            
            if (!$user) {
                // Nouvel utilisateur
                $user = User::create([
                    'name' => $request->name,
                    'phone' => $phone,
                    'firebase_uid' => $request->firebase_uid,
                    'password' => Hash::make(Str::random(16)),
                    'points_total' => 0,
                    'phone_verified' => true,
                ]);
                Log::info('Nouvel utilisateur créé via Firebase', ['user_id' => $user->id, 'phone' => $phone]);
            } else {
                // Mettre à jour l'utilisateur existant
                $user->update([
                    'firebase_uid' => $request->firebase_uid,
                    'phone_verified' => true,
                    'last_login_at' => now(),
                ]);
                Log::info('Utilisateur connecté via Firebase', ['user_id' => $user->id, 'phone' => $phone]);
            }

            // Stocker les infos utilisateur en session
            session([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_phone' => $user->phone,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Connexion réussie',
                'redirect' => '/matches',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Firebase callback error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la connexion. Veuillez réessayer.'
            ], 500);
        }
    }

    /**
     * Vérifie le token Firebase via l'API Google
     */
    private function verifyFirebaseToken(string $token): bool
    {
        $projectId = config('services.firebase.project_id');
        
        if (!$projectId) {
            // Firebase non configuré, on fait confiance au client
            return true;
        }

        try {
            // Vérifier le token via Firebase Admin (simplifié)
            // En production, utilisez firebase-php/php-jwt ou kreait/firebase-php
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token
            ])->get("https://www.googleapis.com/identitytoolkit/v3/relyingparty/getAccountInfo?key=" . config('services.firebase.api_key'), [
                'idToken' => $token
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::warning('Firebase token verification failed: ' . $e->getMessage());
            return false;
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
     * Formate le numéro de téléphone
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
