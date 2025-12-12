<?php

namespace App\Jobs;

use App\Models\MatchGame;
use App\Models\PointLog;
use App\Models\Prediction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
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

        // Get all predictions for this match
        $predictions = Prediction::with('user')
            ->where('match_id', $this->matchId)
            ->get();

        Log::info("ProcessMatchPoints: Processing {$predictions->count()} predictions for match {$this->matchId}");

        foreach ($predictions as $prediction) {
            $totalPoints = 0;

            // 1. Participation (+1 point, une seule fois par match/prédiction)
            $alreadyParticipation = \App\Models\PointLog::where('user_id', $prediction->user_id)
                ->where('source', 'prediction_participation')
                ->where('match_id', $this->matchId)
                ->exists();
            if (!$alreadyParticipation) {
                $totalPoints += 1;
                \App\Models\PointLog::create([
                    'user_id' => $prediction->user_id,
                    'source' => 'prediction_participation',
                    'points' => 1,
                    'match_id' => $this->matchId,
                ]);
            }

            // 2. Correct Winner (+3 points)
            $predictedWinner = $this->determineWinner($prediction->score_a, $prediction->score_b);
            if ($predictedWinner === $actualWinner) {
                $alreadyWinner = \App\Models\PointLog::where('user_id', $prediction->user_id)
                    ->where('source', 'prediction_winner')
                    ->where('match_id', $this->matchId)
                    ->exists();
                if (!$alreadyWinner) {
                    $totalPoints += 3;
                    \App\Models\PointLog::create([
                        'user_id' => $prediction->user_id,
                        'source' => 'prediction_winner',
                        'points' => 3,
                        'match_id' => $this->matchId,
                    ]);
                }
            }

            // 3. Exact Score (+3 points extra)
            if ($prediction->score_a == $match->score_a && $prediction->score_b == $match->score_b) {
                $alreadyExact = \App\Models\PointLog::where('user_id', $prediction->user_id)
                    ->where('source', 'prediction_exact')
                    ->where('match_id', $this->matchId)
                    ->exists();
                if (!$alreadyExact) {
                    $totalPoints += 3;
                    \App\Models\PointLog::create([
                        'user_id' => $prediction->user_id,
                        'source' => 'prediction_exact',
                        'points' => 3,
                        'match_id' => $this->matchId,
                    ]);
                }
            }

            // Update user's total points
            if ($totalPoints > 0 && $prediction->user) {
                $prediction->user->increment('points_total', $totalPoints);
            }

            // Update prediction with total points earned (calculate from actual point_logs)
            $totalPointsEarned = \App\Models\PointLog::where('user_id', $prediction->user_id)
                ->where('match_id', $this->matchId)
                ->sum('points');
            $prediction->points_earned = $totalPointsEarned;
            $prediction->save();

            Log::info("ProcessMatchPoints: User {$prediction->user_id} earned {$totalPoints} new points (total: {$totalPointsEarned}) for match {$this->matchId}");
        }

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
