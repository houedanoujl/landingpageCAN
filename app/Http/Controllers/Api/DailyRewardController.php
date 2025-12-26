<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PointsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DailyRewardController extends Controller
{
    protected PointsService $pointsService;

    public function __construct(PointsService $pointsService)
    {
        $this->pointsService = $pointsService;
    }

    /**
     * Heartbeat endpoint - called by frontend on visibility change or periodic check.
     * Awards daily points if eligible.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function heartbeat(Request $request): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Non authentifié',
            ], 401);
        }

        $user = Auth::user();
        $result = $this->pointsService->awardDailyActivityPoints($user);

        return response()->json([
            'success' => true,
            'awarded' => $result['awarded'],
            'points_awarded' => $result['points'],
            'total_points' => $result['total'],
            'message' => $result['awarded'] 
                ? '🎉 Bravo ! +1 point pour votre connexion quotidienne !' 
                : null,
        ]);
    }

    /**
     * Check if user is eligible for daily reward (without awarding).
     * Useful for frontend to show pending reward indicator.
     * 
     * @return JsonResponse
     */
    public function checkEligibility(): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'eligible' => false,
            ], 401);
        }

        $user = Auth::user();
        $eligible = $this->pointsService->isEligibleForDailyReward($user);

        return response()->json([
            'success' => true,
            'eligible' => $eligible,
            'total_points' => $user->points_total,
        ]);
    }
}
