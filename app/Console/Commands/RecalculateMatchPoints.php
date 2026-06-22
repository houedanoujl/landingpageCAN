<?php

namespace App\Console\Commands;

use App\Models\MatchGame;
use App\Services\PointsService;
use Illuminate\Console\Command;

/**
 * Recalcule les points d'UN match après correction du score (ex. erreur API).
 *
 * Contrairement à user:recalculate-points (qui réinitialise TOUT l'historique
 * d'un joueur), cette commande est chirurgicale : elle n'annule et ne réattribue
 * que les points liés au résultat de ce match précis, pour tous les joueurs.
 *
 *   php artisan matches:recalculate-points 42
 */
class RecalculateMatchPoints extends Command
{
    protected $signature = 'matches:recalculate-points {match : ID du match à recalculer}';

    protected $description = 'Annule puis réattribue les points d\'un match selon le score enregistré (correction de score)';

    public function handle(PointsService $points): int
    {
        $matchId = (int) $this->argument('match');
        $match = MatchGame::find($matchId);

        if (!$match) {
            $this->error("❌ Match #{$matchId} introuvable.");
            return self::FAILURE;
        }

        if ($match->status !== 'finished') {
            $this->warn("⚠️  Le match #{$matchId} n'est pas terminé (statut: {$match->status}). Aucun recalcul.");
            return self::SUCCESS;
        }

        $this->info("⚽ Match #{$match->id} : {$match->team_a} {$match->score_a}-{$match->score_b} {$match->team_b}");
        $this->line('⚙️  Recalcul (annuler puis rejouer)...');

        $summary = $points->recalculateMatchPoints($match);

        if ($summary['skipped'] ?? false) {
            $this->warn('ℹ️  Recalcul ignoré : l\'attribution des points est désactivée (tournoi terminé).');
            return self::SUCCESS;
        }

        $delta = $summary['points_after'] - $summary['points_before'];

        $this->newLine();
        $this->info('✅ Recalcul terminé.');
        $this->table(
            ['Métrique', 'Valeur'],
            [
                ['Joueurs concernés', $summary['users_affected']],
                ['Points avant', $summary['points_before']],
                ['Points après', $summary['points_after']],
                ['Variation', ($delta >= 0 ? '+' : '') . $delta],
            ]
        );

        return self::SUCCESS;
    }
}
