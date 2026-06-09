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
        // Propagation automatique du bracket (Coupe du Monde 2026) :
        // quand un match à élimination directe est terminé, le vainqueur
        // (et le perdant des demi-finales, pour la 3e place) remplit les
        // slots "W{n}" / "L{n}" des matchs suivants.
        // DÉSACTIVÉ PAR DÉFAUT (game.auto_bracket_propagation=false) :
        // l'admin préfère placer les équipes des phases finales à la main.
        if (!config('game.auto_bracket_propagation', false)) {
            return;
        }

        if ($matchGame->wasChanged('status') && $matchGame->status === 'finished') {
            if (in_array($matchGame->phase, ['round_of_32', 'round_of_16', 'quarter_final', 'semi_final'])) {
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
