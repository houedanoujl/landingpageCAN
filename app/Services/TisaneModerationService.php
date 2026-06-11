<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Détection de contenu abusif via l'API Tisane.ai (https://tisane.ai).
 *
 * Couche complémentaire à la liste noire locale : attrape les insultes
 * nouvelles ou contournées que la liste ne connaît pas encore. Supporte
 * le français et l'arabe (entre ~30 langues).
 *
 * Sécurité d'exploitation : fail-open. Si la clé n'est pas configurée,
 * que l'API est lente ou en panne, on retourne null et seule la liste
 * noire locale fait foi — un incident Tisane ne bloque jamais le site.
 */
class TisaneModerationService
{
    /**
     * Analyse un texte.
     *
     * @return string|null  ContentModerationService::LEVEL_BLOCK si abus grave,
     *                      LEVEL_REVIEW si abus probable, LEVEL_CLEAN si rien,
     *                      null si l'API est indisponible/non configurée.
     */
    public function check(string $text): ?string
    {
        $apiKey = config('services.tisane.key');
        if (!$apiKey) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                    'Ocp-Apim-Subscription-Key' => $apiKey,
                ])
                ->timeout((int) config('services.tisane.timeout', 3))
                ->post(config('services.tisane.endpoint', 'https://api.tisane.ai/parse'), [
                    'language' => config('services.tisane.language', 'fr'),
                    'content' => $text,
                    'settings' => ['snippets' => false],
                ]);

            if (!$response->successful()) {
                Log::warning('Tisane: réponse non valide', ['status' => $response->status()]);
                return null;
            }

            $abuse = (array) $response->json('abuse', []);
            if (empty($abuse)) {
                return ContentModerationService::LEVEL_CLEAN;
            }

            // Sévérités Tisane : low | medium | high | extreme
            foreach ($abuse as $finding) {
                if (in_array($finding['severity'] ?? '', ['high', 'extreme'], true)) {
                    return ContentModerationService::LEVEL_BLOCK;
                }
            }

            // Abus détecté mais de sévérité faible/moyenne : modération humaine.
            return ContentModerationService::LEVEL_REVIEW;
        } catch (\Throwable $e) {
            Log::warning('Tisane: API indisponible', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
