<?php

namespace App\Services;

use App\Models\User;
use App\Models\MatchGame;
use App\Models\Prediction;
use App\Models\PointLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PointsService
{
    /**
     * Award daily login points.
     * Limit 1x/day.
     */
    public function awardDailyLoginPoints(User $user): void
    {
        $today = Carbon::today();

        // Check if user already logged in today and got points
        $alreadyAwarded = PointLog::where('user_id', $user->id)
            ->where('source', 'login')
            ->whereDate('created_at', $today)
            ->exists();

        if (!$alreadyAwarded) {
            DB::transaction(function () use ($user) {
                $user->increment('points_total', 1);
                PointLog::create([
                    'user_id' => $user->id,
                    'source' => 'login',
                    'points' => 1,
                ]);
            });
        }
    }

    /**
     * Award points for bar visit (geofencing).
     * Limit 1x/day.
     */
    public function awardBarVisitPoints(User $user): void
    {
        $today = Carbon::today();

        $alreadyAwarded = PointLog::where('user_id', $user->id)
            ->where('source', 'bar_visit')
            ->whereDate('created_at', $today)
            ->exists();

        if (!$alreadyAwarded) {
             DB::transaction(function () use ($user) {
                $user->increment('points_total', 4);
                PointLog::create([
                    'user_id' => $user->id,
                    'source' => 'bar_visit',
                    'points' => 4,
                ]);
            });
        }
    }

    /**
     * Calculate points for a finished match for all predictions.
     * Triggered when a Match is updated to "finished".
     */
    public function calculateMatchPoints(MatchGame $match): void
    {
        if ($match->status !== 'finished') {
            return;
        }

        $predictions = Prediction::where('match_id', $match->id)->get();

        foreach ($predictions as $prediction) {
            $points = 0;

            // Base point for participating
            // Assuming this is awarded immediately or here.
            // The requirement says:
            // - +1 point for participating.
            // - +3 points for correct winner.
            // - +3 points for exact score.

            // Let's assume the +1 for participating is awarded when the prediction is made or handled separately.
            // However, the requirement says "Prediction: +1 point for participating... Trigger this calculation when a Match is updated to 'finished'".
            // If the +1 is part of the calculation triggered on finish, we add it here.
            // If it was already given, we shouldn't give it again.
            // To be safe and idempotent, let's assume we recalculate total earned for this match.

            // Wait, if we recalculate, we need to know if points were already given.
            // A simpler approach: Calculate the *total* points this prediction deserves, and update the prediction record.
            // Then update the user's total points.
            // But updating user's total points needs to be differential if we run this multiple times.
            // Or we wipe the points for this prediction and re-add them.

            // Implementation:
            // 1. Calculate points.
            // 2. Update Prediction->points_earned.
            // 3. Log the points in PointLog (maybe aggregated or careful about duplicates).

            // Let's assume this runs once when status changes to finished.

            $points += 1; // Participation

            // Determine actual winner
            $actualWinner = 'draw';
            if ($match->score_a > $match->score_b) {
                $actualWinner = 'team_a';
            } elseif ($match->score_b > $match->score_a) {
                $actualWinner = 'team_b';
            }

            // Correct Winner
            if ($prediction->predicted_winner === $actualWinner) {
                $points += 3;
            }

            // Exact Score
            if ($prediction->score_a == $match->score_a && $prediction->score_b == $match->score_b) {
                $points += 3;
            }

            // Update prediction and user points
            if ($prediction->points_earned != $points) {
                $diff = $points - $prediction->points_earned;

                // We need the user model
                $user = User::find($prediction->user_id);

                DB::transaction(function () use ($user, $prediction, $points, $diff) {
                     $prediction->update(['points_earned' => $points]);

                     if ($user) {
                         $user->increment('points_total', $diff);

                         // Log it?
                         if ($diff > 0) {
                             PointLog::create([
                                'user_id' => $user->id,
                                'source' => 'prediction',
                                'points' => $diff,
                            ]);
                         }
                     }
                });
            }
        }
    }
}
