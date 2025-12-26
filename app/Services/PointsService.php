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
     *
     * @param int|null $barId The ID of the bar visited
     * @return int Points awarded (0 if already awarded today)
     */
    public function awardBarVisitPoints(User $user, ?int $barId = null): int
    {
        $today = Carbon::today();

        $alreadyAwarded = PointLog::where('user_id', $user->id)
            ->where('source', 'bar_visit')
            ->whereDate('created_at', $today)
            ->exists();

        if (!$alreadyAwarded) {
             DB::transaction(function () use ($user, $barId) {
                $user->increment('points_total', 4);
                PointLog::create([
                    'user_id' => $user->id,
                    'bar_id' => $barId,
                    'source' => 'bar_visit',
                    'points' => 4,
                ]);
            });
            return 4;
        }

        return 0;
    }

    /**
     * Award points for prediction made in a venue (geofencing).
     * Limit 1x/day. User gets 4 points ONLY if the match is actually being shown at this venue.
     *
     * @param int $matchId The ID of the match being predicted
     * @param int|null $barId The ID of the bar where prediction was made
     * @return int Points awarded (0 if already awarded today or match not at this venue)
     */
    public function awardPredictionVenuePoints(User $user, int $matchId, ?int $barId = null): int
    {
        if (!$barId) {
            return 0;
        }

        // Vérifier que le match a bien lieu dans ce bar (via table animations)
        $matchAtVenue = \App\Models\Animation::where('match_id', $matchId)
            ->where('bar_id', $barId)
            ->where('is_active', true)
            ->exists();

        if (!$matchAtVenue) {
            // Le match n'a pas lieu dans ce bar, pas de bonus
            return 0;
        }

        $today = Carbon::today();

        $alreadyAwarded = PointLog::where('user_id', $user->id)
            ->where('source', 'venue_visit')
            ->whereDate('created_at', $today)
            ->exists();

        if (!$alreadyAwarded) {
            DB::transaction(function () use ($user, $barId, $matchId) {
                $user->increment('points_total', 4);
                PointLog::create([
                    'user_id' => $user->id,
                    'bar_id' => $barId,
                    'match_id' => $matchId,
                    'source' => 'venue_visit',
                    'points' => 4,
                ]);
            });
            return 4;
        }

        return 0;
    }

    /**
     * Award daily activity points.
     * This is triggered by the DailyRewardMiddleware on the user's first activity
     * of each calendar day. Works even for users who never log out.
     * 
     * Limit: 1 point per calendar day.
     * 
     * @param User $user
     * @return array{awarded: bool, points: int, total: int}
     */
    public function awardDailyActivityPoints(User $user): array
    {
        $today = Carbon::today();

        // Double-check to avoid race conditions
        $alreadyAwarded = PointLog::where('user_id', $user->id)
            ->where('source', 'daily_activity')
            ->whereDate('created_at', $today)
            ->exists();

        if ($alreadyAwarded) {
            return [
                'awarded' => false,
                'points' => 0,
                'total' => $user->points_total,
            ];
        }

        DB::transaction(function () use ($user, $today) {
            $user->increment('points_total', 1);
            $user->update(['last_daily_reward_at' => $today]);
            
            PointLog::create([
                'user_id' => $user->id,
                'source' => 'daily_activity',
                'points' => 1,
            ]);
        });

        // Refresh user to get updated points
        $user->refresh();

        return [
            'awarded' => true,
            'points' => 1,
            'total' => $user->points_total,
        ];
    }

    /**
     * Check if user is eligible for daily reward (without awarding).
     * Useful for frontend to show notifications.
     * 
     * @param User $user
     * @return bool
     */
    public function isEligibleForDailyReward(User $user): bool
    {
        $today = Carbon::today();
        
        return is_null($user->last_daily_reward_at) || $user->last_daily_reward_at->lt($today);
    }

    /**
     * Calculate points for a finished match for all predictions.
     * Triggered when a Match is updated to "finished".
     * This method now delegates to the ProcessMatchPoints job for consistency.
     */
    public function calculateMatchPoints(MatchGame $match): void
    {
        if ($match->status !== 'finished') {
            return;
        }

        // Dispatch the job to process match points
        // This ensures consistency with the main points calculation system
        \App\Jobs\ProcessMatchPoints::dispatch($match->id);
    }
}
