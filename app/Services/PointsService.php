<?php

namespace App\Services;

use App\Models\User;
use App\Models\MatchGame;
use App\Models\Prediction;
use App\Models\PointLog;
use App\Models\SiteSetting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PointsService
{
    /**
     * Sources qui comptent toutes pour le SEUL point "connexion quotidienne".
     * 'login' = connexion explicite, 'daily_activity' = première activité du jour
     * pour les sessions qui ne se reconnectent jamais. Elles partagent le même
     * plafond : +1 point par jour calendaire, jamais 2.
     */
    private const DAILY_POINT_SOURCES = ['login', 'daily_activity'];

    /**
     * Indique si l'utilisateur a déjà reçu son point quotidien aujourd'hui,
     * quelle que soit la source (login OU activité).
     */
    private function hasDailyPointToday(User $user): bool
    {
        return PointLog::where('user_id', $user->id)
            ->whereIn('source', self::DAILY_POINT_SOURCES)
            ->whereDate('created_at', Carbon::today())
            ->exists();
    }

    /**
     * Award daily login points.
     * Limit 1x/day (partagé avec daily_activity).
     */
    public function awardDailyLoginPoints(User $user): void
    {
        // Check if tournament has ended - no more points
        if (!SiteSetting::isPointsEnabled()) {
            return;
        }

        if (!$this->hasDailyPointToday($user)) {
            DB::transaction(function () use ($user) {
                $user->increment('points_total', 1);
                $user->update(['last_daily_reward_at' => Carbon::today()]);
                PointLog::create([
                    'user_id' => $user->id,
                    'source' => 'login',
                    'points' => 1,
                ]);
            });
        }
    }

    /**
     * Sources qui comptent toutes pour le SEUL bonus venue de +4/jour.
     * RÈGLE MÉTIER : les 4 points "visite lieu partenaire" exigent un check-in
     * + un pronostic (attribués via awardPredictionVenuePoints). Le plafond est
     * partagé entre les deux sources historiques pour interdire tout cumul +8.
     */
    private const VENUE_BONUS_SOURCES = ['venue_visit', 'bar_visit'];

    /**
     * Indique si l'utilisateur a déjà reçu son bonus venue (+4) aujourd'hui,
     * quelle que soit la source historique.
     */
    private function hasVenueBonusToday(User $user): bool
    {
        return PointLog::where('user_id', $user->id)
            ->whereIn('source', self::VENUE_BONUS_SOURCES)
            ->whereDate('created_at', Carbon::today())
            ->exists();
    }

    /**
     * Award points for bar visit (geofencing).
     *
     * DÉPRÉCIÉ pour l'attribution directe : la règle métier exige check-in
     * + pronostic (voir awardPredictionVenuePoints). Conservé pour l'API
     * historique, mais partage le même plafond quotidien de +4.
     *
     * @param int|null $barId The ID of the bar visited
     * @return int Points awarded (0 if already awarded today)
     */
    public function awardBarVisitPoints(User $user, ?int $barId = null): int
    {
        // Check if tournament has ended - no more points
        if (!SiteSetting::isPointsEnabled()) {
            return 0;
        }

        if (!$this->hasVenueBonusToday($user)) {
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
        // Check if tournament has ended - no more points
        if (!SiteSetting::isPointsEnabled()) {
            return 0;
        }

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

        // Plafond quotidien partagé avec bar_visit : max +4/jour toutes sources venue.
        if (!$this->hasVenueBonusToday($user)) {
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
     * Award the +1 participation point immediately when a prediction is made.
     *
     * Idempotent per match: a user can only earn the participation point once
     * per match (source = prediction_participation + match_id). This is the same
     * guard used by ProcessMatchPoints, so awarding here prevents the job from
     * awarding it again when the match finishes.
     *
     * @param User $user
     * @param int $matchId
     * @return int Points awarded (1 the first time, 0 afterwards)
     */
    public function awardPredictionParticipationPoints(User $user, int $matchId): int
    {
        // Check if tournament has ended - no more points
        if (!SiteSetting::isPointsEnabled()) {
            return 0;
        }

        $alreadyAwarded = PointLog::where('user_id', $user->id)
            ->where('source', 'prediction_participation')
            ->where('match_id', $matchId)
            ->exists();

        if ($alreadyAwarded) {
            return 0;
        }

        DB::transaction(function () use ($user, $matchId) {
            $user->increment('points_total', 1);
            PointLog::create([
                'user_id' => $user->id,
                'source' => 'prediction_participation',
                'points' => 1,
                'match_id' => $matchId,
            ]);
        });

        return 1;
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
        // Check if tournament has ended - no more points
        if (!SiteSetting::isPointsEnabled()) {
            return [
                'awarded' => false,
                'points' => 0,
                'total' => $user->points_total,
            ];
        }

        $today = Carbon::today();

        // Plafond partagé avec 'login' : pas de double point quotidien.
        if ($this->hasDailyPointToday($user)) {
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

        // Calcul immédiat et garanti (sans dépendre d'un worker de queue)
        \App\Jobs\ProcessMatchPoints::dispatchSync($match->id);
    }
}
