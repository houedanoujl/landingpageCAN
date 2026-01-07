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
     * Récupère le TOP 15 national pour une période donnée
     * avec noms abrégés (Prénom + initiale du nom)
     */
    public function getTop15(string $period = 'global'): array
    {
        return $this->getTopN($period, 15);
    }

    /**
     * Récupère le TOP 5 national pour une période donnée (legacy)
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
     * 
     * Critères de tri :
     * 1. Points (décroissant)
     * 2. En cas d'égalité : date du premier pronostic (le plus ancien en premier)
     */
    public function getTopN(string $period = 'global', int $limit = 5): array
    {
        $cacheKey = "leaderboard_top{$limit}_{$period}";
        
        return Cache::remember($cacheKey, 60, function () use ($period, $limit) {
            if ($period === 'global') {
                // Classement global basé sur points_total
                // En cas d'égalité, celui qui a fait son premier pronostic en premier gagne
                $users = User::select('users.*')
                    ->selectSub(function ($query) {
                        $query->from('predictions')
                            ->whereColumn('predictions.user_id', 'users.id')
                            ->selectRaw('MIN(created_at)');
                    }, 'first_prediction_at')
                    ->orderBy('points_total', 'desc')
                    ->orderBy('first_prediction_at', 'asc') // Le plus ancien pronostic en premier
                    ->orderBy('name', 'asc') // En dernier recours, alphabétique
                    ->take($limit)
                    ->get();
                
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
     * 
     * Critères de départage en cas d'égalité :
     * - Date du premier pronostic (le plus ancien en premier)
     */
    public function getUserPosition(int $userId, string $period = 'global'): array
    {
        if ($period === 'global') {
            $user = User::find($userId);
            if (!$user) {
                return ['rank' => null, 'points' => 0];
            }

            // Récupérer la date du premier pronostic de l'utilisateur
            $userFirstPrediction = \App\Models\Prediction::where('user_id', $userId)
                ->min('created_at');

            // Compter les utilisateurs avec plus de points
            $rank = User::where('points_total', '>', $user->points_total)->count() + 1;
            
            // En cas d'égalité, compter ceux qui ont fait leur premier pronostic avant
            if ($userFirstPrediction) {
                $samePointsBeforeMe = User::where('points_total', '=', $user->points_total)
                    ->where('id', '!=', $userId)
                    ->whereHas('predictions', function ($query) use ($userFirstPrediction) {
                        $query->havingRaw('MIN(created_at) < ?', [$userFirstPrediction]);
                    })
                    ->count();
                
                // Fallback: compter aussi par nom alphabétique pour ceux sans pronostic ou même date
                $samePointsSameOrNoDate = User::where('points_total', '=', $user->points_total)
                    ->where('id', '!=', $userId)
                    ->where('name', '<', $user->name)
                    ->whereDoesntHave('predictions')
                    ->count();
                
                $rank += $samePointsBeforeMe + $samePointsSameOrNoDate;
            } else {
                // L'utilisateur n'a pas de pronostic, tri alphabétique
                $samePointsBeforeMe = User::where('points_total', '=', $user->points_total)
                    ->where('name', '<', $user->name)
                    ->count();
                $rank += $samePointsBeforeMe;
            }

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
     * 
     * Critères de tri :
     * 1. Points (décroissant)
     * 2. En cas d'égalité : date du premier pronostic de la période (le plus ancien en premier)
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

        // Récupérer la date du premier pronostic pour chaque utilisateur dans cette période
        $firstPredictionByUser = \App\Models\Prediction::select('user_id', DB::raw('MIN(created_at) as first_prediction_at'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('user_id')
            ->pluck('first_prediction_at', 'user_id');

        // Joindre avec les informations utilisateur
        $rankings = [];

        foreach ($pointsByUser as $entry) {
            $user = User::find($entry->user_id);
            if (!$user) continue;

            $rankings[] = [
                'rank' => 0, // Sera calculé après le tri
                'name' => $this->abbreviateName($user->name),
                'full_name' => $user->name,
                'points' => (int) $entry->period_points,
                'user_id' => $user->id,
                'first_prediction_at' => $firstPredictionByUser[$user->id] ?? null,
            ];
        }

        // Trier par points desc, puis par date du premier pronostic (le plus ancien en premier)
        usort($rankings, function ($a, $b) {
            // 1. D'abord par points (décroissant)
            if ($a['points'] !== $b['points']) {
                return $b['points'] - $a['points'];
            }
            
            // 2. En cas d'égalité, par date du premier pronostic (le plus ancien en premier)
            $dateA = $a['first_prediction_at'];
            $dateB = $b['first_prediction_at'];
            
            if ($dateA && $dateB) {
                $comparison = strcmp($dateA, $dateB);
                if ($comparison !== 0) {
                    return $comparison;
                }
            } elseif ($dateA && !$dateB) {
                return -1; // A a un pronostic, B non -> A en premier
            } elseif (!$dateA && $dateB) {
                return 1; // B a un pronostic, A non -> B en premier
            }
            
            // 3. En dernier recours, alphabétique
            return strcmp($a['full_name'], $b['full_name']);
        });

        // Réattribuer les rangs après le tri
        $rank = 1;
        foreach ($rankings as &$entry) {
            $entry['rank'] = $rank++;
            unset($entry['first_prediction_at']); // Nettoyer les données internes
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
     * Récupère les gagnants d'une semaine (TOP 15)
     */
    public function getWeeklyWinners(string $period): array
    {
        $rankings = $this->calculatePeriodRankings($period);
        return array_slice($rankings, 0, 15);
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
                    'is_winner' => $index < 15, // Top 15 sont gagnants
                ]
            );
        }

        // Invalider le cache
        Cache::forget("leaderboard_top15_{$period}");
        Cache::forget("leaderboard_top5_{$period}");
        Cache::forget("leaderboard_top20_{$period}");
        Cache::forget("leaderboard_top3_{$period}");
        Cache::forget("leaderboard_top10_{$period}");
    }

    /**
     * Récupère les données complètes du leaderboard pour l'affichage
     * - Global: Top 20
     * - Hebdomadaire: Top 15
     */
    public function getLeaderboardData(?int $userId = null, string $period = 'global'): array
    {
        // Déterminer si c'est une période hebdomadaire
        $isWeekly = str_starts_with($period, 'week_');
        
        // Global = Top 20, Hebdomadaire = Top 15
        $topLimit = $isWeekly ? 15 : 20;
        $topUsers = $this->getTopN($period, $topLimit);
        
        $userPosition = $userId ? $this->getUserPosition($userId, $period) : null;
        $availablePeriods = WeeklyRanking::getAvailablePeriods();
        $currentPeriod = WeeklyRanking::getCurrentPeriod();

        // Vérifier si l'utilisateur est dans le classement gagnant
        $userInTop = false;
        if ($userId) {
            foreach ($topUsers as $entry) {
                if ($entry['user_id'] === $userId) {
                    $userInTop = true;
                    break;
                }
            }
        }

        return [
            'top15' => $isWeekly ? $topUsers : array_slice($topUsers, 0, 15),
            'top20' => !$isWeekly ? $topUsers : [],
            'top5' => array_slice($topUsers, 0, 5), // Compatibilité
            'user_position' => $userPosition,
            'user_in_top15' => $userInTop,
            'user_in_top20' => $userInTop,
            'user_in_top5' => $userInTop, // Compatibilité
            'is_weekly' => $isWeekly,
            'top_limit' => $topLimit,
            'available_periods' => $availablePeriods,
            'current_period' => $currentPeriod,
            'selected_period' => $period,
            'period_label' => $period === 'global' 
                ? 'Classement Général' 
                : WeeklyRanking::getPeriodLabel($period),
        ];
    }
}
