<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessMatchPoints;
use App\Models\Bar;
use App\Models\MatchGame;
use App\Models\Prediction;
use App\Models\Team;
use App\Models\User;
use App\Models\SiteSetting;
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
            'totalBars' => Bar::count(),
            'activeBars' => Bar::where('is_active', true)->count(),
            'totalPredictions' => Prediction::count(),
            'totalTeams' => Team::count(),
        ];

        $recentMatches = MatchGame::with(['homeTeam', 'awayTeam'])
            ->orderBy('match_date', 'desc')
            ->take(10)
            ->get();

        $topUsers = User::orderBy('points_total', 'desc')->take(10)->get();

        return view('admin.dashboard', compact('stats', 'recentMatches', 'topUsers'));
    }

    // ==================== MATCHES ====================

    /**
     * List all matches for management
     */
    public function matches(Request $request)
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $query = MatchGame::with(['homeTeam', 'awayTeam']);

        // Filtre par recherche (équipe, groupe, date)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('team_a', 'like', "%{$search}%")
                  ->orWhere('team_b', 'like', "%{$search}%")
                  ->orWhere('group_name', 'like', "%{$search}%")
                  ->orWhere('stadium', 'like', "%{$search}%")
                  ->orWhereDate('match_date', 'like', "%{$search}%");
            });
        }

        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $matches = $query->orderBy('match_date', 'asc')->get();

        return view('admin.matches', compact('matches'));
    }

    /**
     * Show create form for a match
     */
    public function createMatch()
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $teams = Team::orderBy('name')->get();

        return view('admin.create-match', compact('teams'));
    }

    /**
     * Store a new match
     */
    public function storeMatch(Request $request)
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $request->validate([
            'home_team_id' => 'required|exists:teams,id',
            'away_team_id' => 'required|exists:teams,id|different:home_team_id',
            'match_date' => 'required|date',
            'group_name' => 'nullable|string|max:50',
            'status' => 'required|in:scheduled,live,finished',
        ]);

        $homeTeam = Team::find($request->home_team_id);
        $awayTeam = Team::find($request->away_team_id);

        MatchGame::create([
            'team_a' => $homeTeam->name,
            'team_b' => $awayTeam->name,
            'home_team_id' => $request->home_team_id,
            'away_team_id' => $request->away_team_id,
            'match_date' => $request->match_date,
            'group_name' => $request->group_name,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.matches')->with('success', 'Match créé avec succès.');
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
        $teams = Team::orderBy('name')->get();

        return view('admin.edit-match', compact('match', 'teams'));
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
            'home_team_id' => 'required|exists:teams,id',
            'away_team_id' => 'required|exists:teams,id',
            'match_date' => 'required|date',
            'group_name' => 'nullable|string|max:50',
            'score_a' => 'nullable|integer|min:0|max:20',
            'score_b' => 'nullable|integer|min:0|max:20',
            'status' => 'required|in:scheduled,live,finished',
        ]);

        $match = MatchGame::findOrFail($id);
        
        $wasScheduled = $match->status === 'scheduled';
        $nowFinished = $request->status === 'finished';

        $homeTeam = Team::find($request->home_team_id);
        $awayTeam = Team::find($request->away_team_id);

        $match->update([
            'team_a' => $homeTeam->name,
            'team_b' => $awayTeam->name,
            'home_team_id' => $request->home_team_id,
            'away_team_id' => $request->away_team_id,
            'match_date' => $request->match_date,
            'group_name' => $request->group_name,
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
     * Delete a match
     */
    public function deleteMatch($id)
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $match = MatchGame::findOrFail($id);
        
        // Supprimer les pronostics associés
        Prediction::where('match_id', $id)->delete();
        
        $match->delete();

        return redirect()->route('admin.matches')->with('success', 'Match supprimé avec succès.');
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

    // ==================== USERS ====================

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
     * Show edit form for a user
     */
    public function editUser($id)
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $user = User::findOrFail($id);

        return view('admin.edit-user', compact('user'));
    }

    /**
     * Update user details
     */
    public function updateUser(Request $request, $id)
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'role' => 'required|in:user,admin',
            'points_total' => 'required|integer|min:0',
        ]);

        $user = User::findOrFail($id);
        $user->update($request->only(['name', 'phone', 'email', 'role', 'points_total']));

        return redirect()->route('admin.users')->with('success', 'Utilisateur mis à jour avec succès.');
    }

    /**
     * Delete a user
     */
    public function deleteUser($id)
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $user = User::findOrFail($id);
        
        // Ne pas supprimer si c'est le dernier admin
        if ($user->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
            return back()->with('error', 'Impossible de supprimer le dernier administrateur.');
        }

        // Supprimer les pronostics associés
        Prediction::where('user_id', $id)->delete();
        
        $user->delete();

        return redirect()->route('admin.users')->with('success', 'Utilisateur supprimé avec succès.');
    }

    // ==================== BARS (Points de Vente) ====================

    /**
     * List all bars
     */
    public function bars()
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $bars = Bar::orderBy('name')->paginate(20);

        return view('admin.bars', compact('bars'));
    }

    /**
     * Show create form for a bar
     */
    public function createBar()
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        return view('admin.create-bar');
    }

    /**
     * Store a new bar
     */
    public function storeBar(Request $request)
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'is_active' => 'boolean',
        ]);

        Bar::create([
            'name' => $request->name,
            'address' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.bars')->with('success', 'Point de vente créé avec succès.');
    }

    /**
     * Show edit form for a bar
     */
    public function editBar($id)
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $bar = Bar::findOrFail($id);

        return view('admin.edit-bar', compact('bar'));
    }

    /**
     * Update bar details
     */
    public function updateBar(Request $request, $id)
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'is_active' => 'boolean',
        ]);

        $bar = Bar::findOrFail($id);
        $bar->update([
            'name' => $request->name,
            'address' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.bars')->with('success', 'Point de vente mis à jour avec succès.');
    }

    /**
     * Toggle bar active status
     */
    public function toggleBar($id)
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $bar = Bar::findOrFail($id);
        $bar->update(['is_active' => !$bar->is_active]);

        $status = $bar->is_active ? 'activé' : 'désactivé';
        return back()->with('success', "Point de vente {$status} avec succès.");
    }

    /**
     * Delete a bar
     */
    public function deleteBar($id)
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $bar = Bar::findOrFail($id);
        $bar->delete();

        return redirect()->route('admin.bars')->with('success', 'Point de vente supprimé avec succès.');
    }

    // ==================== TEAMS ====================

    /**
     * List all teams
     */
    public function teams()
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $teams = Team::orderBy('name')->paginate(30);

        return view('admin.teams', compact('teams'));
    }

    /**
     * Show create form for a team
     */
    public function createTeam()
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        return view('admin.create-team');
    }

    /**
     * Store a new team
     */
    public function storeTeam(Request $request)
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:teams',
            'iso_code' => 'required|string|size:2|unique:teams',
            'group_name' => 'nullable|string|max:50',
        ]);

        Team::create([
            'name' => $request->name,
            'iso_code' => strtolower($request->iso_code),
            'group_name' => $request->group_name,
        ]);

        return redirect()->route('admin.teams')->with('success', 'Équipe créée avec succès.');
    }

    /**
     * Show edit form for a team
     */
    public function editTeam($id)
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $team = Team::findOrFail($id);

        return view('admin.edit-team', compact('team'));
    }

    /**
     * Update team details
     */
    public function updateTeam(Request $request, $id)
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $team = Team::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:teams,name,' . $id,
            'iso_code' => 'required|string|size:2|unique:teams,iso_code,' . $id,
            'group_name' => 'nullable|string|max:50',
        ]);

        $team->update([
            'name' => $request->name,
            'iso_code' => strtolower($request->iso_code),
            'group_name' => $request->group_name,
        ]);

        return redirect()->route('admin.teams')->with('success', 'Équipe mise à jour avec succès.');
    }

    /**
     * Delete a team
     */
    public function deleteTeam($id)
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $team = Team::findOrFail($id);
        
        // Vérifier si l'équipe est utilisée dans des matchs
        $matchCount = MatchGame::where('home_team_id', $id)->orWhere('away_team_id', $id)->count();
        if ($matchCount > 0) {
            return back()->with('error', "Impossible de supprimer cette équipe car elle est utilisée dans {$matchCount} match(s).");
        }
        
        $team->delete();

        return redirect()->route('admin.teams')->with('success', 'Équipe supprimée avec succès.');
    }

    // ==================== PREDICTIONS ====================

    /**
     * List all predictions
     */
    public function predictions(Request $request)
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $query = Prediction::with(['user', 'match.homeTeam', 'match.awayTeam'])
            ->orderBy('created_at', 'desc');

        // Filtre par match
        if ($request->has('match_id') && $request->match_id) {
            $query->where('match_id', $request->match_id);
        }

        // Filtre par utilisateur
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // Filtre par statut de match (onglets)
        $status = $request->get('status', 'all');
        if ($status === 'upcoming') {
            $query->whereHas('match', function ($q) {
                $q->whereIn('status', ['scheduled', 'live']);
            });
        } elseif ($status === 'finished') {
            $query->whereHas('match', function ($q) {
                $q->where('status', 'finished');
            });
        }

        $predictions = $query->paginate(50)->withQueryString();

        // Statistiques
        $totalPredictions = Prediction::count();
        $upcomingPredictions = Prediction::whereHas('match', function ($q) {
            $q->whereIn('status', ['scheduled', 'live']);
        })->count();
        $finishedPredictions = Prediction::whereHas('match', function ($q) {
            $q->where('status', 'finished');
        })->count();
        $totalPointsAwarded = Prediction::sum('points_earned');
        $avgPointsPerPrediction = $finishedPredictions > 0
            ? round(Prediction::whereHas('match', function ($q) {
                $q->where('status', 'finished');
            })->avg('points_earned'), 2)
            : 0;

        $matches = MatchGame::orderBy('match_date', 'desc')->get();
        $users = User::orderBy('name')->get();

        return view('admin.predictions', compact(
            'predictions',
            'matches',
            'users',
            'status',
            'totalPredictions',
            'upcomingPredictions',
            'finishedPredictions',
            'totalPointsAwarded',
            'avgPointsPerPrediction'
        ));
    }

    /**
     * Delete a prediction
     */
    public function deletePrediction($id)
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $prediction = Prediction::findOrFail($id);
        $prediction->delete();

        return back()->with('success', 'Pronostic supprimé avec succès.');
    }

    // ==================== SETTINGS ====================

    /**
     * Show settings form
     */
    public function settings()
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $settings = SiteSetting::pluck('value', 'key')->toArray();

        return view('admin.settings', compact('settings'));
    }

    /**
     * Update settings
     */
    public function updateSettings(Request $request)
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $settingsToSave = [
            'site_name' => $request->input('site_name', 'CAN 2025'),
            'geofencing_radius' => $request->input('geofencing_radius', 200),
            'points_exact_score' => $request->input('points_exact_score', 10),
            'points_correct_winner' => $request->input('points_correct_winner', 5),
            'points_correct_draw' => $request->input('points_correct_draw', 3),
            'maintenance_mode' => $request->has('maintenance_mode') ? '1' : '0',
        ];

        foreach ($settingsToSave as $key => $value) {
            SiteSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return back()->with('success', 'Paramètres mis à jour avec succès.');
    }

    // ==================== GESTION DU TOURNOI ====================

    /**
     * Afficher la page de gestion du tournoi
     */
    public function tournamentManagement()
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        // Récupérer tous les groupes de la phase de poules
        $groups = MatchGame::where('phase', 'group_stage')
            ->whereNotNull('group_name')
            ->distinct()
            ->pluck('group_name')
            ->sort();

        $groupStandings = [];
        foreach ($groups as $group) {
            $service = app(\App\Services\TournamentService::class);
            $groupStandings[$group] = $service->calculateGroupStandings($group);
        }

        // Statistiques des phases
        $phaseStats = [
            'group_stage' => MatchGame::where('phase', 'group_stage')->count(),
            'round_of_16' => MatchGame::where('phase', 'round_of_16')->count(),
            'quarter_final' => MatchGame::where('phase', 'quarter_final')->count(),
            'semi_final' => MatchGame::where('phase', 'semi_final')->count(),
            'third_place' => MatchGame::where('phase', 'third_place')->count(),
            'final' => MatchGame::where('phase', 'final')->count(),
        ];

        return view('admin.tournament', compact('groups', 'groupStandings', 'phaseStats'));
    }

    /**
     * Générer le tableau à élimination directe
     */
    public function generateKnockoutBracket()
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        try {
            $service = app(\App\Services\TournamentService::class);
            $bracket = $service->createKnockoutBracket();

            return back()->with('success', 'Tableau à élimination directe créé avec succès ! ' .
                '8 matchs de 1/8e, 4 quarts, 2 demis, 1 finale et 1 match pour la 3e place.');

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    /**
     * Qualifier manuellement une équipe pour un match
     */
    public function qualifyTeam(Request $request, $matchId)
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $request->validate([
            'team_id' => 'required|exists:teams,id',
            'position' => 'required|in:home,away',
        ]);

        $match = MatchGame::findOrFail($matchId);
        $team = Team::findOrFail($request->team_id);

        if ($request->position === 'home') {
            $match->update([
                'home_team_id' => $team->id,
                'team_a' => $team->name,
            ]);
        } else {
            $match->update([
                'away_team_id' => $team->id,
                'team_b' => $team->name,
            ]);
        }

        return back()->with('success', "{$team->name} qualifié(e) pour ce match !");
    }

    /**
     * Afficher les matchs d'une phase spécifique
     */
    public function phaseMatches($phase)
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $validPhases = ['group_stage', 'round_of_16', 'quarter_final', 'semi_final', 'third_place', 'final'];

        if (!in_array($phase, $validPhases)) {
            return redirect()->route('admin.tournament')->with('error', 'Phase invalide.');
        }

        $matches = MatchGame::where('phase', $phase)
            ->with(['homeTeam', 'awayTeam', 'parentMatch1', 'parentMatch2'])
            ->orderBy('display_order')
            ->orderBy('match_date')
            ->get();

        $phaseNames = [
            'group_stage' => 'Phase de poules',
            'round_of_16' => '1/8e de finale',
            'quarter_final' => 'Quarts de finale',
            'semi_final' => 'Demi-finales',
            'third_place' => 'Match pour la 3e place',
            'final' => 'Finale',
        ];

        $phaseName = $phaseNames[$phase] ?? $phase;

        // Récupérer toutes les équipes pour la sélection manuelle
        $teams = Team::orderBy('name')->get();

        return view('admin.phase-matches', compact('matches', 'phase', 'phaseName', 'teams'));
    }

    /**
     * Calculer les qualifiés depuis les poules
     */
    public function calculateQualified()
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        try {
            $service = app(\App\Services\TournamentService::class);
            $result = $service->qualifyTeamsFromGroupStage();

            $qualifiedCount = collect($result['qualified_teams'])
                ->map(fn($g) => [$g['first'], $g['second']])
                ->flatten()
                ->count();

            $bestThirdsCount = $result['best_thirds']->count();

            return back()->with('success',
                "Calcul terminé ! {$qualifiedCount} équipes (1ers et 2es) + {$bestThirdsCount} meilleurs 3èmes = " .
                ($qualifiedCount + $bestThirdsCount) . " équipes qualifiées pour les 1/8e."
            );

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }
}
