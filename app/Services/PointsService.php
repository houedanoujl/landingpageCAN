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
     * @return int Points awarded (0 if already awarded today)
     */
    public function awardBarVisitPoints(User $user): int
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
            return 4;
        }

        return 0;
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
