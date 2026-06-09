<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Services\PointsService;
use Symfony\Component\HttpFoundation\Response;

class DailyRewardMiddleware
{
    protected PointsService $pointsService;

    public function __construct(PointsService $pointsService)
    {
        $this->pointsService = $pointsService;
    }

    /**
     * Handle an incoming request.
     * 
     * Award daily points on the user's first activity of each calendar day.
     * This works even for users who never log out.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // L'app authentifie via session('user_id') (pas le guard Auth de Laravel).
        // RefreshUserPoints, exécuté avant, restaure cette session depuis le cookie
        // remember_token le cas échéant.
        $userId = session('user_id');

        if ($userId) {
            $user = User::find($userId);
            $today = Carbon::today();

            // Première activité du jour : on tente le point quotidien.
            // Le plafond partagé (login/daily_activity) évite tout double comptage.
            if ($user && (is_null($user->last_daily_reward_at) || $user->last_daily_reward_at->lt($today))) {
                $this->pointsService->awardDailyActivityPoints($user);
            }
        }

        return $next($request);
    }
}
