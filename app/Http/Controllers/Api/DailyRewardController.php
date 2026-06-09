<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PointsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DailyRewardController extends Controller
{
    protected PointsService $pointsService;

    public function __construct(PointsService $pointsService)
    {
        $this->pointsService = $pointsService;
    }

    /**
     * Résout l'utilisateur courant via la session applicative (session('user_id')),
     * cohérent avec le reste de l'app (l'auth Laravel n'est pas utilisée).
     */
    private function currentUser(): ?User
    {
        $userId = session('user_id');

        return $userId ? User::find($userId) : null;
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
        $user = $this->currentUser();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Non authentifié',
            ], 401);
        }

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
        $user = $this->currentUser();

        if (!$user) {
            return response()->json([
                'success' => false,
                'eligible' => false,
            ], 401);
        }

        $eligible = $this->pointsService->isEligibleForDailyReward($user);

        return response()->json([
            'success' => true,
            'eligible' => $eligible,
            'total_points' => $user->points_total,
        ]);
    }
}
