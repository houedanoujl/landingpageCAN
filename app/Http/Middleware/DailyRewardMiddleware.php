<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
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
        if (Auth::check()) {
            $user = Auth::user();
            $today = Carbon::today();
            
            // Compare dates: if last_daily_reward_at is null or before today, award points
            if (is_null($user->last_daily_reward_at) || $user->last_daily_reward_at->lt($today)) {
                $this->pointsService->awardDailyActivityPoints($user);
            }
        }

        return $next($request);
    }
}
