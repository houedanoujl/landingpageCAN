<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MatchGame;
use App\Models\User;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Fetch upcoming matches (example logic)
        $upcomingMatches = MatchGame::where('status', 'scheduled')
            ->orderBy('match_date', 'asc')
            ->take(3)
            ->get();

        // Fetch top 3 users for a mini leaderboard
        $topUsers = User::orderBy('points_total', 'desc')->take(3)->get();

        return view('welcome', compact('upcomingMatches', 'topUsers'));
    }

    public function matches()
    {
        $matches = MatchGame::orderBy('match_date', 'asc')->get();
        return view('matches', compact('matches'));
    }

    public function leaderboard()
    {
        $users = User::orderBy('points_total', 'desc')->paginate(20);
        return view('leaderboard', compact('users'));
    }
}
