<?php

namespace App\Services;

use App\Models\User;
use App\Models\Bar;
use App\Models\MatchGame;
use App\Models\Prediction;
use App\Models\PointLog;
use App\Models\SiteSetting;
use App\Models\AdminAuditLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
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
     * Sources qui comptent pour le bonus venue de +4.
     * RÈGLE MÉTIER 2026 : « Le bonus de +4 points est conservé uniquement si un
     * pronostic est effectué le même jour après le check-in, avec un plafonnement
     * à un seul check-in par point de vente et par jour. » Un simple check-in sans
     * pronostic ne rapporte rien ; le +4 est accordé au pronostic fait sur place,
     * plafonné à un seul par (utilisateur, PDV, jour). 'bar_visit' n'est conservé
     * que pour les logs historiques.
     */
    private const VENUE_BONUS_SOURCES = ['venue_visit', 'bar_visit'];

    /**
     * Indique si l'utilisateur a déjà reçu son bonus venue (+4) aujourd'hui pour
     * CE point de vente — plafond d'un seul +4 par (utilisateur, PDV, jour).
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
     * +4 pour un pronostic fait sur place dans un PDV actif, plafonné à un seul
     * +4 par (utilisateur, PDV, jour). Tout PDV actif y donne droit (avec ou sans
     * animation programmée).
     *
     * @param int $matchId The ID of the match being predicted
     * @param int|null $barId The ID of the bar where prediction was made
     * @return int Points awarded (0 if already awarded today for this venue)
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

        // INVARIANT « +4 ⇔ check-in depuis un POINT DE VENTE ». Le bonus n'est
        // accordé que si $barId désigne un PDV existant ET actif. Garde-fou central :
        // même si un appelant transmettait un bar_id non vérifié (ou un PDV désactivé),
        // aucun +4 ne serait attribué. La proximité GPS est, elle, vérifiée côté
        // appelant (findNearbyVenue / isVenueProximityVerified) avant cet appel.
        if (!Bar::where('id', $barId)->where('is_active', true)->exists()) {
            return 0;
        }

        // Plafond : un seul +4 par (utilisateur, PDV, jour).
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

    /**
     * Sources de points qui dépendent du RÉSULTAT d'un match.
     * Ce sont les SEULES annulées lors d'une correction de score. Les bonus de
     * check-in (venue_visit / bar_visit) et les points de connexion (login /
     * daily_activity) ne dépendent pas du score et ne doivent JAMAIS être retirés
     * ici.
     */
    private const MATCH_RESULT_SOURCES = [
        'prediction_participation',
        'prediction_winner',
        'prediction_exact',
    ];

    /**
     * Corrige (recalcule) les points d'un match déjà terminé après modification
     * du score par l'admin — typiquement quand l'API s'est trompée.
     *
     * Le cœur du recalcul vit dans ProcessMatchPoints, qui est AUTO-CORRECTIF
     * (il annule les points de résultat existants puis réattribue selon le score
     * enregistré). Cette méthode ne fait donc que l'ENROBER pour l'usage admin :
     *   - garde-fou « points désactivés » (tournoi terminé) ;
     *   - mesure de l'écart avant/après ;
     *   - traçabilité (AdminAuditLog) de l'action manuelle ;
     *   - résumé renvoyé à l'UI / la commande.
     *
     * Idempotent et sûr à rejouer : appelée deux fois de suite sans changement de
     * score, elle reproduit exactement le même état.
     *
     * @return array{
     *     skipped: bool,
     *     reason?: string,
     *     users_affected: int,
     *     points_before: int,
     *     points_after: int,
     *     points_removed: int
     * }
     */
    public function recalculateMatchPoints(MatchGame $match): array
    {
        $before = (int) PointLog::where('match_id', $match->id)
            ->whereIn('source', self::MATCH_RESULT_SOURCES)
            ->sum('points');

        // Si l'attribution des points est désactivée (tournoi terminé), ne rien
        // faire : ProcessMatchPoints s'arrêterait sur ce même interrupteur sans
        // réattribuer. On évite ainsi tout retrait de points sans contrepartie.
        if (!SiteSetting::isPointsEnabled()) {
            return [
                'skipped'        => true,
                'reason'         => 'points_disabled',
                'users_affected' => 0,
                'points_before'  => $before,
                'points_after'   => $before,
                'points_removed' => 0,
            ];
        }

        // Recalcul délégué au job auto-correctif : annule les points de résultat
        // existants puis réattribue selon le score enregistré, en une transaction.
        if ($match->status === 'finished' && $match->score_a !== null && $match->score_b !== null) {
            \App\Jobs\ProcessMatchPoints::dispatchSync($match->id);
        }

        $after = (int) PointLog::where('match_id', $match->id)
            ->whereIn('source', self::MATCH_RESULT_SOURCES)
            ->sum('points');

        $removed = max(0, $before - $after);

        $usersAffected = (int) PointLog::where('match_id', $match->id)
            ->whereIn('source', self::MATCH_RESULT_SOURCES)
            ->distinct()
            ->count('user_id');

        // Traçabilité : qui a recalculé, quand, et l'écart de points.
        AdminAuditLog::record(
            'match.recalculate_points',
            "Recalcul des points — match #{$match->id} : {$match->team_a} {$match->score_a}-{$match->score_b} {$match->team_b}",
            $match,
            [
                'score_a'        => $match->score_a,
                'score_b'        => $match->score_b,
                'winner'         => $match->winner,
                'points_before'  => $before,
                'points_after'   => $after,
                'points_removed' => $removed,
            ]
        );

        // Le classement a potentiellement changé.
        Cache::forget('leaderboard_top_5');

        return [
            'skipped'        => false,
            'users_affected' => $usersAffected,
            'points_before'  => $before,
            'points_after'   => $after,
            'points_removed' => $removed,
        ];
    }
}
