<?php

namespace App\Services;

use App\Models\PointLog;
use App\Models\User;
use App\Models\WeeklyRanking;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LeaderboardService
{
    /**
     * Récupère le TOP 5 national pour une période donnée
     * avec noms abrégés (Prénom + initiale du nom)
     */
    public function getTop5(string $period = 'global'): array
    {
        return $this->getTopN($period, 5);
    }

    /**
     * Récupère le TOP 20 national pour une période donnée
     * avec noms abrégés (Prénom + initiale du nom)
     */
    public function getTop20(string $period = 'global'): array
    {
        return $this->getTopN($period, 20);
    }

    /**
     * Récupère le TOP N national pour une période donnée
     * avec noms abrégés (Prénom + initiale du nom)
     */
    public function getTopN(string $period = 'global', int $limit = 5): array
    {
        $cacheKey = "leaderboard_top{$limit}_{$period}";
        
        return Cache::remember($cacheKey, 60, function () use ($period, $limit) {
            if ($period === 'global') {
                // Classement global basé sur points_total
                $users = User::orderBy('points_total', 'desc')
                    ->orderBy('name', 'asc')
                    ->take($limit)
                    ->get(['id', 'name', 'points_total']);
                
                return $users->map(function ($user, $index) {
                    return [
                        'rank' => $index + 1,
                        'name' => $this->abbreviateName($user->name),
                        'points' => $user->points_total,
                        'user_id' => $user->id,
                    ];
                })->toArray();
            }

            // Classement par période (semaine ou demi-finale)
            $periodData = WeeklyRanking::PERIODS[$period] ?? null;
            
            if (!$periodData) {
                return [];
            }

            // Calculer les points pour cette période
            $rankings = $this->calculatePeriodRankings($period);
            
            return array_slice($rankings, 0, $limit);
        });
    }

    /**
     * Récupère la position et les points de l'utilisateur connecté
     */
    public function getUserPosition(int $userId, string $period = 'global'): array
    {
        if ($period === 'global') {
            $user = User::find($userId);
            if (!$user) {
                return ['rank' => null, 'points' => 0];
            }

            // Compter les utilisateurs avec plus de points
            $rank = User::where('points_total', '>', $user->points_total)->count() + 1;
            
            // En cas d'égalité, trier par nom
            $samePointsBeforeMe = User::where('points_total', '=', $user->points_total)
                ->where('name', '<', $user->name)
                ->count();
            
            $rank += $samePointsBeforeMe;

            return [
                'rank' => $rank,
                'points' => $user->points_total,
                'total_users' => User::count(),
            ];
        }

        // Position pour une période spécifique
        $rankings = $this->calculatePeriodRankings($period);
        $userRanking = collect($rankings)->firstWhere('user_id', $userId);

        if (!$userRanking) {
            // L'utilisateur n'a pas de points cette période
            return [
                'rank' => count($rankings) + 1,
                'points' => 0,
                'total_users' => count($rankings),
            ];
        }

        return [
            'rank' => $userRanking['rank'],
            'points' => $userRanking['points'],
            'total_users' => count($rankings),
        ];
    }

    /**
     * Calcule les classements pour une période donnée
     * Tous les points comptent (pronostics, login, visite bar, etc.)
     */
    public function calculatePeriodRankings(string $period): array
    {
        $periodData = WeeklyRanking::PERIODS[$period] ?? null;
        
        if (!$periodData) {
            return [];
        }

        $startDate = $periodData['start'] . ' 00:00:00';
        $endDate = $periodData['end'] . ' 23:59:59';

        // Tous les points gagnés pendant cette période
        $pointsByUser = PointLog::select('user_id', DB::raw('SUM(points) as period_points'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('user_id')
            ->orderByDesc('period_points')
            ->get();

        // Joindre avec les informations utilisateur
        $rankings = [];
        $rank = 1;
        $previousPoints = null;
        $sameRankCount = 0;

        foreach ($pointsByUser as $entry) {
            $user = User::find($entry->user_id);
            if (!$user) continue;

            // Gérer les égalités
            if ($previousPoints !== null && $entry->period_points < $previousPoints) {
                $rank += $sameRankCount;
                $sameRankCount = 1;
            } else {
                $sameRankCount++;
            }

            $rankings[] = [
                'rank' => $rank,
                'name' => $this->abbreviateName($user->name),
                'full_name' => $user->name,
                'points' => (int) $entry->period_points,
                'user_id' => $user->id,
            ];

            $previousPoints = $entry->period_points;
        }

        // Trier par points desc puis par nom asc pour les égalités
        usort($rankings, function ($a, $b) {
            if ($a['points'] !== $b['points']) {
                return $b['points'] - $a['points'];
            }
            return strcmp($a['full_name'], $b['full_name']);
        });

        // Réattribuer les rangs après le tri
        $rank = 1;
        foreach ($rankings as &$entry) {
            $entry['rank'] = $rank++;
        }

        return $rankings;
    }

    /**
     * Abrège le nom : "Jean Dupont" -> "Jean D."
     */
    public function abbreviateName(string $fullName): string
    {
        $parts = explode(' ', trim($fullName));
        
        if (count($parts) === 1) {
            return $parts[0];
        }

        $firstName = $parts[0];
        $lastInitial = strtoupper(substr($parts[count($parts) - 1], 0, 1));

        return "{$firstName} {$lastInitial}.";
    }

    /**
     * Récupère les gagnants d'une semaine (TOP 5)
     */
    public function getWeeklyWinners(string $period): array
    {
        $rankings = $this->calculatePeriodRankings($period);
        return array_slice($rankings, 0, 5);
    }

    /**
     * Sauvegarde les classements hebdomadaires (à exécuter à la fin de chaque semaine)
     */
    public function saveWeeklyRankings(string $period): void
    {
        $rankings = $this->calculatePeriodRankings($period);

        foreach ($rankings as $index => $entry) {
            WeeklyRanking::updateOrCreate(
                [
                    'user_id' => $entry['user_id'],
                    'period' => $period,
                ],
                [
                    'points' => $entry['points'],
                    'rank' => $entry['rank'],
                    'is_winner' => $index < 5, // Top 5 sont gagnants
                ]
            );
        }

        // Invalider le cache
        Cache::forget("leaderboard_top5_{$period}");
    }

    /**
     * Récupère les données complètes du leaderboard pour l'affichage
     */
    public function getLeaderboardData(?int $userId = null, string $period = 'global'): array
    {
        $top5 = $this->getTop5($period);
        $top20 = $this->getTop20($period);
        $userPosition = $userId ? $this->getUserPosition($userId, $period) : null;
        $availablePeriods = WeeklyRanking::getAvailablePeriods();
        $currentPeriod = WeeklyRanking::getCurrentPeriod();

        // Vérifier si l'utilisateur est dans le TOP 5
        $userInTop5 = false;
        if ($userId) {
            foreach ($top5 as $entry) {
                if ($entry['user_id'] === $userId) {
                    $userInTop5 = true;
                    break;
                }
            }
        }

        return [
            'top5' => $top5,
            'top20' => $top20,
            'user_position' => $userPosition,
            'user_in_top5' => $userInTop5,
            'available_periods' => $availablePeriods,
            'current_period' => $currentPeriod,
            'selected_period' => $period,
            'period_label' => $period === 'global' 
                ? 'Classement Général' 
                : WeeklyRanking::getPeriodLabel($period),
        ];
    }
}
