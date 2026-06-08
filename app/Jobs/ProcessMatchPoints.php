<?php

namespace App\Jobs;

use App\Models\MatchGame;
use App\Models\PointLog;
use App\Models\Prediction;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessMatchPoints implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $matchId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $matchId)
    {
        $this->matchId = $matchId;
    }

    /**
     * Execute the job.
     *
     * Scoring Rules:
     * - Participation: +1 point (awarded on prediction, not here)
     * - Correct Winner (1/N/2): +3 points
     * - Exact Score: +3 points extra
     * - Total max per match: 7 points (1 + 3 + 3)
     */
    public function handle(): void
    {
        // Check if tournament has ended - no more points
        if (!SiteSetting::isPointsEnabled()) {
            Log::info("ProcessMatchPoints: Tournament ended, skipping points for match {$this->matchId}");
            return;
        }

        $match = MatchGame::find($this->matchId);

        if (!$match || $match->status !== 'finished') {
            Log::warning("ProcessMatchPoints: Match {$this->matchId} not found or not finished");
            return;
        }

        if ($match->score_a === null || $match->score_b === null) {
            Log::warning("ProcessMatchPoints: Match {$this->matchId} has no final score");
            return;
        }

        // Determine actual match result
        $actualWinner = $this->determineWinner($match->score_a, $match->score_b);
        
        // Vérifier si le match réel a eu des tirs au but
        // Un match a des TAB si le score est égal ET qu'il y a un vainqueur défini
        $matchHadPenalties = ($match->score_a == $match->score_b) && !empty($match->winner);
        if ($matchHadPenalties) {
            $actualWinner = $match->winner; // home, away
        }

        // Get all predictions for this match
        $predictions = Prediction::where('match_id', $this->matchId)->get();

        if ($predictions->isEmpty()) {
            Log::info("ProcessMatchPoints: No predictions for match {$this->matchId}");
            return;
        }

        Log::info("ProcessMatchPoints: Processing {$predictions->count()} predictions for match {$this->matchId}");

        // Pré-charger en une seule requête tous les PointLogs déjà attribués
        // pour ce match, indexés par utilisateur puis par source.
        // Évite 3 requêtes "exists()" par pronostic (N+1).
        $existingLogs = PointLog::where('match_id', $this->matchId)
            ->whereIn('source', ['prediction_participation', 'prediction_winner', 'prediction_exact'])
            ->get(['user_id', 'source'])
            ->groupBy('user_id')
            ->map(fn ($rows) => $rows->pluck('source')->flip());

        $now = now();
        $newLogs = [];          // lignes PointLog à insérer en masse
        $userDeltas = [];       // user_id => points à ajouter au total

        foreach ($predictions as $prediction) {
            $userId = $prediction->user_id;
            $awarded = $existingLogs->get($userId) ?? collect();

            // 1. Participation (+1 point, une seule fois par match)
            if (!$awarded->has('prediction_participation')) {
                $newLogs[] = [
                    'user_id' => $userId,
                    'source' => 'prediction_participation',
                    'points' => 1,
                    'match_id' => $this->matchId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $userDeltas[$userId] = ($userDeltas[$userId] ?? 0) + 1;
            }

            // Vérifier si l'utilisateur a prédit des tirs au but
            $userPredictedPenalties = $prediction->predict_draw && $prediction->penalty_winner;

            // 2. Correct Winner (+3 points)
            if ($matchHadPenalties && $userPredictedPenalties) {
                $predictedWinner = $prediction->penalty_winner;
            } elseif ($matchHadPenalties && !$userPredictedPenalties) {
                $predictedWinner = $this->determineWinner($prediction->score_a, $prediction->score_b);
            } elseif (!$matchHadPenalties && $userPredictedPenalties) {
                $predictedWinner = $prediction->penalty_winner;
            } else {
                $predictedWinner = $this->determineWinner($prediction->score_a, $prediction->score_b);
            }

            if ($predictedWinner === $actualWinner && !$awarded->has('prediction_winner')) {
                $newLogs[] = [
                    'user_id' => $userId,
                    'source' => 'prediction_winner',
                    'points' => 3,
                    'match_id' => $this->matchId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $userDeltas[$userId] = ($userDeltas[$userId] ?? 0) + 3;
            }

            // 3. Exact Score (+3 points extra)
            // RÈGLE: pas de score exact si le match s'est joué aux tirs au but.
            if (!$matchHadPenalties
                && $prediction->score_a == $match->score_a
                && $prediction->score_b == $match->score_b
                && !$awarded->has('prediction_exact')
            ) {
                $newLogs[] = [
                    'user_id' => $userId,
                    'source' => 'prediction_exact',
                    'points' => 3,
                    'match_id' => $this->matchId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $userDeltas[$userId] = ($userDeltas[$userId] ?? 0) + 3;
            }
        }

        // Tout est écrit en une transaction, avec des opérations groupées.
        DB::transaction(function () use ($newLogs, $userDeltas, $predictions) {
            // Insertion en masse des nouveaux points (1 requête)
            if (!empty($newLogs)) {
                PointLog::insert($newLogs);
            }

            // Mise à jour des totaux utilisateurs (uniquement ceux qui ont gagné des points)
            foreach ($userDeltas as $userId => $delta) {
                if ($delta > 0) {
                    User::where('id', $userId)->increment('points_total', $delta);
                }
            }

            // Recalcule points_earned par pronostic à partir des point_logs (1 requête agrégée)
            $totalsByUser = PointLog::where('match_id', $this->matchId)
                ->whereIn('user_id', $predictions->pluck('user_id'))
                ->selectRaw('user_id, SUM(points) as total')
                ->groupBy('user_id')
                ->pluck('total', 'user_id');

            foreach ($predictions as $prediction) {
                $earned = (int) ($totalsByUser[$prediction->user_id] ?? 0);
                if ((int) $prediction->points_earned !== $earned) {
                    $prediction->points_earned = $earned;
                    $prediction->save();
                }
            }
        });

        // Clear leaderboard cache since points changed
        Cache::forget('leaderboard_top_5');

        Log::info("ProcessMatchPoints: Completed processing match {$this->matchId}");
    }

    /**
     * Determine winner from scores.
     * 
     * @return string 'home' | 'away' | 'draw'
     */
    private function determineWinner(int $homeScore, int $awayScore): string
    {
        if ($homeScore > $awayScore) {
            return 'home';
        } elseif ($awayScore > $homeScore) {
            return 'away';
        }
        return 'draw';
    }
}
