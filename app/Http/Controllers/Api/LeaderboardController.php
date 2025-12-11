<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class LeaderboardController extends Controller
{
    public function index(Request $request)
    {
        // Cache top 5 users for 5 minutes to handle high traffic
        $topUsers = Cache::remember('leaderboard_top_5', 300, function () {
            return User::orderBy('points_total', 'desc')
                ->take(5)
                ->get(['id', 'name', 'points_total']);
        });

        $currentUser = Auth::user();
        $userRank = User::orderBy('points_total', 'desc')
            ->where('points_total', '>', $currentUser->points_total)
            ->count() + 1;

        return response()->json([
            'top_users' => $topUsers,
            'user_rank' => $userRank,
            'user_points' => $currentUser->points_total,
        ]);
    }
}
