<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Bar;
use App\Models\MatchGame;
use App\Models\PointLog;
use App\Models\Prediction;
use App\Models\User;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Fetch upcoming matches with team relationships
        $upcomingMatches = MatchGame::with(['homeTeam', 'awayTeam'])
            ->where('status', 'scheduled')
            ->orderBy('match_date', 'asc')
            ->take(3)
            ->get();

        // Fetch top 5 users for leaderboard
        $topUsers = User::orderBy('points_total', 'desc')->take(5)->get();
        
        // Count venues for stats
        $venueCount = Bar::where('is_active', true)->count();

        return view('welcome', compact('upcomingMatches', 'topUsers', 'venueCount'));
    }

    public function venues()
    {
        $venues = Bar::where('is_active', true)->orderBy('name')->get();
        return view('venues', compact('venues'));
    }

    public function matches(Request $request)
    {
        // Vérifier si un point de vente est sélectionné
        $venueId = $request->query('venue') ?? session('selected_venue_id');
        $selectedVenue = null;
        
        if ($venueId) {
            $selectedVenue = Bar::find($venueId);
            if ($selectedVenue) {
                session(['selected_venue_id' => $venueId]);
            }
        }

        // Si pas de point de vente sélectionné, rediriger vers la sélection
        if (!$selectedVenue) {
            return redirect()->route('venues')->with('error', 'Veuillez d\'abord sélectionner un point de vente.');
        }

        $matches = MatchGame::with(['homeTeam', 'awayTeam'])
            ->orderBy('group_name', 'asc')
            ->orderBy('match_date', 'asc')
            ->get()
            ->groupBy('group_name');
        
        // Récupérer les pronostics de l'utilisateur connecté
        $userPredictions = [];
        if (session('user_id')) {
            $predictions = Prediction::where('user_id', session('user_id'))->get();
            foreach ($predictions as $prediction) {
                $userPredictions[$prediction->match_id] = $prediction;
            }
        }
        
        return view('matches', compact('matches', 'userPredictions', 'selectedVenue'));
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

    public function dashboard()
    {
        if (!session('user_id')) {
            return redirect('/login')->with('error', 'Veuillez vous connecter.');
        }

        $userId = session('user_id');
        $user = User::find($userId);

        if (!$user) {
            return redirect('/login')->with('error', 'Utilisateur non trouvé.');
        }

        // Calculate rank
        $rank = User::where('points_total', '>', $user->points_total)->count() + 1;

        // Get prediction stats
        $predictionCount = Prediction::where('user_id', $userId)->count();
        $correctPredictions = Prediction::where('user_id', $userId)
            ->where('points_earned', '>', 0)
            ->count();

        // Get venue visits
        $venueVisits = PointLog::where('user_id', $userId)
            ->whereIn('source', ['venue_visit', 'bar_visit'])
            ->count();

        // Next match
        $nextMatch = MatchGame::with(['homeTeam', 'awayTeam'])
            ->where('status', 'scheduled')
            ->where('match_date', '>', now())
            ->orderBy('match_date')
            ->first();

        // Recent predictions
        $recentPredictions = Prediction::with('match')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'user',
            'rank',
            'predictionCount',
            'correctPredictions',
            'venueVisits',
            'nextMatch',
            'recentPredictions'
        ));
    }
}
