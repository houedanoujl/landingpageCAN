<?php

namespace App\Observers;

use App\Models\MatchGame;
use App\Services\TournamentService;
use Illuminate\Support\Facades\Log;

class MatchObserver
{
    protected $tournamentService;

    public function __construct(TournamentService $tournamentService)
    {
        $this->tournamentService = $tournamentService;
    }

    /**
     * Handle the MatchGame "created" event.
     */
    public function created(MatchGame $matchGame): void
    {
        //
    }

    /**
     * Handle the MatchGame "updated" event.
     */
    public function updated(MatchGame $matchGame): void
    {
        // Qualification automatique désactivée - tout est géré manuellement par l'admin
        // Pour réactiver, décommentez le code ci-dessous

        /*
        // Si le match vient d'être terminé
        if ($matchGame->isDirty('status') && $matchGame->status === 'finished') {
            Log::info('Match terminé, vérification de la qualification automatique', [
                'match_id' => $matchGame->id,
                'phase' => $matchGame->phase,
            ]);

            // Pour les phases à élimination directe, qualifier automatiquement le gagnant
            if (in_array($matchGame->phase, ['round_of_16', 'quarter_final', 'semi_final'])) {
                try {
                    $this->tournamentService->updateKnockoutMatchTeams($matchGame);
                } catch (\Exception $e) {
                    Log::error('Erreur lors de la qualification automatique', [
                        'match_id' => $matchGame->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
        */
    }

    /**
     * Handle the MatchGame "deleted" event.
     */
    public function deleted(MatchGame $matchGame): void
    {
        //
    }

    /**
     * Handle the MatchGame "restored" event.
     */
    public function restored(MatchGame $matchGame): void
    {
        //
    }

    /**
     * Handle the MatchGame "force deleted" event.
     */
    public function forceDeleted(MatchGame $matchGame): void
    {
        //
    }
}
