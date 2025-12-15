<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Envoie un message WhatsApp via Green API
     */
    public function sendMessage(string $phoneNumber, string $message): array
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

        $payload = [
            'chatId' => $phoneNumber . '@c.us',
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

    /**
     * Envoie une confirmation de pronostic par WhatsApp
     */
    public function sendPredictionConfirmation($user, $match, $prediction, $venue): array
    {
        $teamA = $match->homeTeam->name ?? 'Équipe A';
        $teamB = $match->awayTeam->name ?? 'Équipe B';
        $matchDate = $match->match_date->format('d/m/Y à H:i');
        $stadium = $match->stadium ?? 'Stade non défini';

        $message = "🎯 *Pronostic enregistré !*\n\n";
        $message .= "{$teamA} {$prediction->score_a} - {$prediction->score_b} {$teamB}\n\n";
        $message .= "📅 {$matchDate}\n";
        $message .= "📍 {$stadium}\n";
        $message .= "🏆 Points potentiels : 1 pt + jusqu'à 6 pts bonus\n\n";
        $message .= "Validé depuis : {$venue->name}";

        $phoneNumber = $this->formatWhatsAppNumber($user->phone);

        return $this->sendMessage($phoneNumber, $message);
    }

    /**
     * Formate un numéro de téléphone pour WhatsApp
     * Extrait de AuthController
     */
    public function formatWhatsAppNumber(string $phone): string
    {
        // Supprimer tous les caractères non numériques
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Côte d'Ivoire : +225 suivi de 13 chiffres -> on garde les 8 derniers
        if (str_starts_with($phone, '225')) {
            // Format attendu: 225XXXXXXXXXX (13 chiffres au total)
            if (strlen($phone) == 13) {
                return substr($phone, 3, 8); // Prend 8 chiffres après 225
            }
        }

        // Sénégal : +221 suivi de 9 chiffres
        if (str_starts_with($phone, '221')) {
            if (strlen($phone) == 12) {
                return substr($phone, 3); // Tout après 221
            }
        }

        // France : +33 suivi de 9 chiffres
        if (str_starts_with($phone, '33')) {
            if (strlen($phone) == 11) {
                return '0' . substr($phone, 2); // Remplace 33 par 0
            }
        }

        // Par défaut, retourner le numéro tel quel
        return $phone;
    }
}
