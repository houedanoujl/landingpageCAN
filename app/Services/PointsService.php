<?php

namespace App\Services;

use App\Models\User;
use App\Models\MatchGame;
use App\Models\Prediction;
use App\Models\PointLog;
use App\Models\SiteSetting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

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
     * Sources qui comptent toutes pour le bonus venue de +4.
     * RÈGLE MÉTIER 2026 : le +4 est accordé UNIQUEMENT lorsqu'un pronostic est
     * enregistré sur place dans un PDV partenaire. Un simple check-in (passage)
     * sans pronostic ne rapporte aucun point — c'est ce qui bloque les check-ins
     * abusifs. Le bonus se cumule entre PDV différents ; le plafond est par
     * couple (utilisateur, PDV, jour) pour interdire un double +4 dans le même
     * PDV le même jour. 'bar_visit' n'est conservé que pour les logs historiques.
     */
    private const VENUE_BONUS_SOURCES = ['venue_visit', 'bar_visit'];

    /**
     * Indique si l'utilisateur a déjà reçu son bonus venue (+4) aujourd'hui
     * pour CE point de vente, quelle que soit la source historique.
     */
    private function hasVenueBonusToday(User $user, ?int $barId = null): bool
    {
        return PointLog::where('user_id', $user->id)
            ->whereIn('source', self::VENUE_BONUS_SOURCES)
            ->when($barId, fn ($q) => $q->where('bar_id', $barId))
            ->whereDate('created_at', Carbon::today())
            ->exists();
    }

    /**
     * Détecte une violation de contrainte d'unicité (doublon) MySQL/PostgreSQL.
     */
    private function isDuplicateKey(QueryException $e): bool
    {
        // SQLSTATE 23000 (MySQL) / 23505 (PostgreSQL) = integrity constraint violation.
        return in_array($e->getCode(), ['23000', '23505'], true);
    }

    /**
     * Award points for prediction made in a venue (geofencing).
     * Limit 1x/day PER venue. Every active venue qualifies, with or without an
     * animation scheduled for the match (règle 2026 : tous les PDV donnent les
     * +4 points, cumulables entre PDV différents le même jour).
     *
     * @param int $matchId The ID of the match being predicted
     * @param int|null $barId The ID of the bar where prediction was made
     * @return int Points awarded (0 if already awarded today for this bar)
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

        // Tous les PDV actifs donnent droit au bonus, qu'une animation soit
        // programmée ou non pour ce match (décision marketing : ne plus exiger
        // d'entrée dans la table animations).

        // Plafond par (utilisateur, PDV, jour), partagé avec bar_visit :
        // pas de double +4 dans le même PDV, mais cumul possible entre PDV différents.
        if (!$this->hasVenueBonusToday($user, $barId)) {
            try {
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
            } catch (QueryException $e) {
                // Doublon bloqué par l'index uniq_venue_bonus_per_day (race condition).
                // La transaction a rollback : aucun point ajouté. On dégrade en 0.
                if ($this->isDuplicateKey($e)) {
                    return 0;
                }
                throw $e;
            }
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
