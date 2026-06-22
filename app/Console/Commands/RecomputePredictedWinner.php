<?php

namespace App\Console\Commands;

use App\Models\Prediction;
use Illuminate\Console\Command;

/**
 * Réconcilie predictions.predicted_winner avec le score pronostiqué.
 *
 * INVARIANT : predicted_winner est TOUJOURS dérivé du score (voir
 * PredictionController::store et le moteur de points ProcessMatchPoints, qui
 * ignore ce champ et recalcule à partir du score). Des données seedées ont pu
 * violer cet invariant (ex. score 0-4 mais predicted_winner='home'), ce qui
 * fausse l'affichage « Vainqueur prédit », les statistiques communautaires et
 * les notifications de résultat — SANS jamais fausser les points.
 *
 * Rapport par défaut ; --fix pour appliquer.
 */
class RecomputePredictedWinner extends Command
{
    protected $signature = 'predictions:recompute-winner {--fix : Appliquer les corrections}';

    protected $description = 'Réaligne predicted_winner sur le score pronostiqué (corrige les données incohérentes)';

    public function handle(): int
    {
        $apply = $this->option('fix');

        $predictions = Prediction::whereNotNull('score_a')
            ->whereNotNull('score_b')
            ->get();

        $mismatched = [];

        foreach ($predictions as $p) {
            $computed = $this->computeWinner($p);
            if ($p->predicted_winner !== $computed) {
                $mismatched[] = [$p->id, $p->user_id, "{$p->score_a}-{$p->score_b}", $p->predicted_winner ?? 'NULL', $computed];
            }
        }

        if (empty($mismatched)) {
            $this->info('✅ Aucun pronostic incohérent : predicted_winner est aligné sur les scores.');
            return self::SUCCESS;
        }

        $this->warn(count($mismatched) . ' pronostic(s) incohérent(s) :');
        $this->table(['Prono', 'User', 'Score', 'Avant', 'Après (= score)'], $mismatched);

        if (!$apply) {
            $this->newLine();
            $this->line('Aperçu uniquement. Relancez avec --fix pour appliquer.');
            return self::SUCCESS;
        }

        $fixed = 0;
        foreach ($predictions as $p) {
            $computed = $this->computeWinner($p);
            if ($p->predicted_winner !== $computed) {
                $p->predicted_winner = $computed;
                $p->saveQuietly(); // pas d'événement/observer : simple correction de données
                $fixed++;
            }
        }

        $this->newLine();
        $this->info("✅ {$fixed} pronostic(s) corrigé(s).");

        return self::SUCCESS;
    }

    /**
     * Même logique que PredictionController::store et ProcessMatchPoints :
     * home/away depuis le score, sinon vainqueur TAB (knockout) ou nul.
     */
    private function computeWinner(Prediction $p): string
    {
        if ($p->score_a > $p->score_b) {
            return 'home';
        }
        if ($p->score_b > $p->score_a) {
            return 'away';
        }
        // Égalité : vainqueur aux tirs au but si pronostiqué (phase à élimination directe).
        if ($p->predict_draw && $p->penalty_winner) {
            return $p->penalty_winner;
        }
        return 'draw';
    }
}
