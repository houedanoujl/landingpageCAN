<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessMatchPoints;
use App\Models\MatchGame;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Check if current user is admin
     */
    private function checkAdmin()
    {
        $userId = session('user_id');
        if (!$userId) {
            return false;
        }
        
        $user = User::find($userId);
        return $user && $user->role === 'admin';
    }

    /**
     * Admin dashboard home
     */
    public function index()
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $stats = [
            'totalUsers' => User::count(),
            'totalMatches' => MatchGame::count(),
            'finishedMatches' => MatchGame::where('status', 'finished')->count(),
            'upcomingMatches' => MatchGame::where('status', 'scheduled')->count(),
        ];

        $recentMatches = MatchGame::with(['homeTeam', 'awayTeam'])
            ->orderBy('match_date', 'desc')
            ->take(10)
            ->get();

        $topUsers = User::orderBy('points_total', 'desc')->take(10)->get();

        return view('admin.dashboard', compact('stats', 'recentMatches', 'topUsers'));
    }

    /**
     * List all matches for management
     */
    public function matches()
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $matches = MatchGame::with(['homeTeam', 'awayTeam'])
            ->orderBy('match_date', 'asc')
            ->get();

        return view('admin.matches', compact('matches'));
    }

    /**
     * Show edit form for a match
     */
    public function editMatch($id)
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $match = MatchGame::with(['homeTeam', 'awayTeam'])->findOrFail($id);

        return view('admin.edit-match', compact('match'));
    }

    /**
     * Update match details (scores and status)
     */
    public function updateMatch(Request $request, $id)
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $request->validate([
            'score_a' => 'nullable|integer|min:0|max:20',
            'score_b' => 'nullable|integer|min:0|max:20',
            'status' => 'required|in:scheduled,finished',
        ]);

        $match = MatchGame::findOrFail($id);
        
        $wasScheduled = $match->status === 'scheduled';
        $nowFinished = $request->status === 'finished';

        $match->update([
            'score_a' => $request->score_a,
            'score_b' => $request->score_b,
            'status' => $request->status,
        ]);

        // If match just finished, trigger points calculation
        if ($wasScheduled && $nowFinished && $request->score_a !== null && $request->score_b !== null) {
            ProcessMatchPoints::dispatch($match->id);
            return redirect()->route('admin.matches')->with('success', "Match mis à jour. Calcul des points en cours...");
        }

        return redirect()->route('admin.matches')->with('success', 'Match mis à jour avec succès.');
    }

    /**
     * List all users
     */
    public function users()
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $users = User::orderBy('points_total', 'desc')->paginate(50);

        return view('admin.users', compact('users'));
    }

    /**
     * Manually trigger points calculation for a match
     */
    public function calculatePoints($id)
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $match = MatchGame::findOrFail($id);

        if ($match->status !== 'finished') {
            return back()->with('error', 'Le match doit être terminé pour calculer les points.');
        }

        ProcessMatchPoints::dispatch($match->id);

        return back()->with('success', 'Calcul des points déclenché pour ce match.');
    }
}
