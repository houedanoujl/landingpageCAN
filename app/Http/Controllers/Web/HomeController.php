<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Bar;
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

        // Fetch top 5 users for leaderboard
        $topUsers = User::orderBy('points_total', 'desc')->take(5)->get();
        
        // Count venues for stats
        $venueCount = Bar::where('is_active', true)->count();

        return view('welcome', compact('upcomingMatches', 'topUsers', 'venueCount'));
    }

    public function matches()
    {
        $matches = MatchGame::orderBy('group_name', 'asc')
            ->orderBy('match_date', 'asc')
            ->get()
            ->groupBy('group_name');
        
        return view('matches', compact('matches'));
    }

    public function leaderboard()
    {
        $users = User::orderBy('points_total', 'desc')->paginate(20);
        return view('leaderboard', compact('users'));
    }

    public function map()
    {
        $venues = Bar::where('is_active', true)->orderBy('name')->get();
        return view('map', compact('venues'));
    }
}
