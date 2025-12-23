<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessMatchPoints;
use App\Models\Bar;
use App\Models\MatchGame;
use App\Models\Prediction;
use App\Models\Stadium;
use App\Models\Team;
use App\Models\User;
use App\Models\SiteSetting;
use App\Models\AdminOtpLog;
use App\Models\Animation;
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

        $query = MatchGame::with(['homeTeam', 'awayTeam', 'animations']);

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

        $matches = $query->orderBy('match_date', 'asc')->paginate(30)->withQueryString();

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
        $stadiums = Stadium::where('is_active', true)->orderBy('city')->get();
        $bars = Bar::where('is_active', true)->orderBy('zone')->orderBy('name')->get();

        // Liste des groupes disponibles pour la CAN
        $groups = ['A', 'B', 'C', 'D', 'E', 'F'];

        return view('admin.create-match', compact('teams', 'stadiums', 'bars', 'groups'));
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
            'phase' => 'required|in:group_stage,round_of_16,quarter_final,semi_final,third_place,final',
            'group_name' => 'nullable|string|max:50',
            'stadium' => 'nullable|string|max:255',
            'status' => 'required|in:scheduled,live,finished',
            'venues' => 'nullable|array',
            'venues.*' => 'exists:bars,id',
        ]);

        $homeTeam = Team::find($request->home_team_id);
        $awayTeam = Team::find($request->away_team_id);

        $match = MatchGame::create([
            'team_a' => $homeTeam->name,
            'team_b' => $awayTeam->name,
            'home_team_id' => $request->home_team_id,
            'away_team_id' => $request->away_team_id,
            'match_date' => $request->match_date,
            'phase' => $request->phase,
            'group_name' => $request->group_name,
            'stadium' => $request->stadium,
            'status' => $request->status,
        ]);

        // Assign venues to the match
        if ($request->has('venues') && is_array($request->venues)) {
            foreach ($request->venues as $venueId) {
                Animation::create([
                    'bar_id' => $venueId,
                    'match_id' => $match->id,
                    'animation_date' => \Carbon\Carbon::parse($request->match_date)->format('Y-m-d'),
                    'animation_time' => \Carbon\Carbon::parse($request->match_date)->format('H:i:s'),
                    'is_active' => true,
                ]);
            }
        }

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

        $match = MatchGame::with(['homeTeam', 'awayTeam', 'animations'])->findOrFail($id);
        $teams = Team::orderBy('name')->get();
        $stadiums = Stadium::where('is_active', true)->orderBy('city')->get();
        $bars = Bar::where('is_active', true)->orderBy('zone')->orderBy('name')->get();

        // IDs des bars déjà assignés à ce match
        $assignedBarIds = $match->animations->pluck('bar_id')->toArray();

        // Liste des groupes disponibles pour la CAN
        $groups = ['A', 'B', 'C', 'D', 'E', 'F'];

        return view('admin.edit-match', compact('match', 'teams', 'stadiums', 'groups', 'bars', 'assignedBarIds'));
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
            'phase' => 'required|in:group_stage,round_of_16,quarter_final,semi_final,third_place,final',
            'group_name' => 'nullable|string|max:50',
            'stadium' => 'nullable|string|max:255',
            'score_a' => 'nullable|integer|min:0|max:20',
            'score_b' => 'nullable|integer|min:0|max:20',
            'status' => 'required|in:scheduled,live,finished',
            'had_penalties' => 'nullable|boolean',
            'winner' => 'nullable|in:home,away',
            'venue_ids' => 'nullable|array',
            'venue_ids.*' => 'exists:bars,id',
        ]);

        $match = MatchGame::findOrFail($id);

        $wasFinished = $match->status === 'finished';
        $nowFinished = $request->status === 'finished';

        $homeTeam = Team::find($request->home_team_id);
        $awayTeam = Team::find($request->away_team_id);

        // Gérer les tirs au but : si had_penalties est true ET score_a == score_b, on stocke le winner
        $winner = null;
        if ($request->had_penalties && $request->score_a == $request->score_b && $request->winner) {
            $winner = $request->winner;
        }

        $match->update([
            'team_a' => $homeTeam->name,
            'team_b' => $awayTeam->name,
            'home_team_id' => $request->home_team_id,
            'away_team_id' => $request->away_team_id,
            'match_date' => $request->match_date,
            'phase' => $request->phase,
            'group_name' => $request->group_name,
            'stadium' => $request->stadium,
            'score_a' => $request->score_a,
            'score_b' => $request->score_b,
            'status' => $request->status,
            'winner' => $winner, // NULL si pas de TAB, 'home' ou 'away' si TAB
        ]);

        // Synchroniser les animations (PDV assignés)
        if ($request->has('venue_ids')) {
            $venueIds = $request->input('venue_ids', []);

            // Supprimer les animations existantes
            Animation::where('match_id', $match->id)->delete();

            // Créer les nouvelles animations
            foreach ($venueIds as $venueId) {
                Animation::create([
                    'bar_id' => $venueId,
                    'match_id' => $match->id,
                    'animation_date' => date('Y-m-d', strtotime($request->match_date)),
                    'animation_time' => date('H:i:s', strtotime($request->match_date)),
                    'is_active' => true,
                ]);
            }
        }

        // Calcul automatique des points si le match vient d'être terminé
        if ($nowFinished && $request->score_a !== null && $request->score_b !== null && !$wasFinished) {
            ProcessMatchPoints::dispatch($match->id);
            return redirect()->route('admin.matches')->with('success', "Match terminé ! Les points sont en cours de calcul...");
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

        // Dispatcher le job de calcul des points
        ProcessMatchPoints::dispatch($match->id);

        // Traiter immédiatement la queue si possible (mode sync)
        if (config('queue.default') === 'sync') {
            return back()->with('success', 'Points recalculés avec succès !');
        }

        return back()->with('success', 'Calcul des points en cours... Rafraîchissez la page dans quelques secondes.');
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

    /**
     * Reset user points to zero and delete all point logs
     */
    public function resetUserPoints($id)
    {
        if (!$this->checkAdmin()) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé.'], 403);
        }

        try {
            $user = User::findOrFail($id);
            
            // Compter les logs avant suppression
            $logsCount = \App\Models\PointLog::where('user_id', $user->id)->count();
            $previousPoints = $user->points_total;
            
            // Supprimer tous les logs de points
            \App\Models\PointLog::where('user_id', $user->id)->delete();
            
            // Réinitialiser les points
            $user->points_total = 0;
            $user->save();
            
            // IMPORTANT: Marquer les pronostics comme "points_earned = 0"
            // pour éviter que les points soient recalculés automatiquement
            \App\Models\Prediction::where('user_id', $user->id)->update(['points_earned' => 0]);
            
            return response()->json([
                'success' => true,
                'message' => "Points réinitialisés avec succès!\n\n" .
                            "• Points supprimés: {$previousPoints} pts\n" .
                            "• Logs supprimés: {$logsCount}\n" .
                            "• Nouveaux points: 0 pts"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    // ==================== BARS (Points de Vente) ====================

    /**
     * List all bars
     */
    public function bars(Request $request)
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $query = Bar::with(['animations.match.homeTeam', 'animations.match.awayTeam']);

        // Filtre par recherche (nom, zone)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('zone', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        // Filtre par zone spécifique
        if ($request->filled('zone')) {
            $query->where('zone', $request->zone);
        }

        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Filtre par présence de matches assignés
        if ($request->filled('has_matches')) {
            if ($request->has_matches === 'yes') {
                $query->has('animations');
            } elseif ($request->has_matches === 'no') {
                $query->doesntHave('animations');
            }
        }

        // Filtre par type de PDV
        if ($request->filled('type_pdv')) {
            $query->where('type_pdv', $request->type_pdv);
        }

        $bars = $query->orderBy('name')->paginate(20);

        // Get unique zones for filter dropdown
        $zones = Bar::whereNotNull('zone')
            ->distinct()
            ->orderBy('zone')
            ->pluck('zone');

        // Get type PDV options
        $typePdvOptions = Bar::getTypePdvOptions();

        return view('admin.bars', compact('bars', 'zones', 'typePdvOptions'));
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
            'zone' => 'nullable|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'is_active' => 'boolean',
        ]);

        Bar::create([
            'name' => $request->name,
            'address' => $request->address,
            'zone' => $request->zone,
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
            'zone' => 'nullable|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'is_active' => 'boolean',
        ]);

        $bar = Bar::findOrFail($id);
        $bar->update([
            'name' => $request->name,
            'address' => $request->address,
            'zone' => $request->zone,
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
        return redirect()->route('admin.bars')->with('success', "Le bar \"{$bar->name}\" a été {$status} avec succès.");
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
        $barName = $bar->name;

        // Supprimer les animations associées
        Animation::where('bar_id', $id)->delete();

        // Supprimer le point de vente
        $bar->delete();

        return redirect()->route('admin.bars')->with('success', "Le point de vente \"{$barName}\" a été supprimé avec succès.");
    }

    /**
     * Download CSV template for bars import
     */
    public function downloadBarsTemplate()
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="modele_points_de_vente.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');

            // BOM UTF-8 pour Excel
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // En-têtes (avec TYPE_PDV optionnel)
            fputcsv($file, ['nom', 'adresse', 'latitude', 'longitude', 'TYPE_PDV']);

            // Exemples avec les différents types
            fputcsv($file, ['Bar Le Sphinx', 'Rue 10 x Avenue Hassan II Dakar', '14.692778', '-17.447938', 'dakar']);
            fputcsv($file, ['Chez Fatou', 'Place de l\'Indépendance Dakar', '14.693350', '-17.448830', 'dakar']);
            fputcsv($file, ['Restaurant Le Teranga', 'Almadies Dakar', '14.741234', '-17.521000', 'chr']);
            fputcsv($file, ['Hotel Djoloff', 'Corniche Ouest Dakar', '14.716677', '-17.481383', 'chr']);
            fputcsv($file, ['Fanzone Stade', 'Avenue Léopold Senghor', '14.683456', '-17.445678', 'fanzone']);
            fputcsv($file, ['Fanzone Place Nation', 'Place de la Nation Dakar', '14.670000', '-17.440000', 'fanzone_public']);
            fputcsv($file, ['Noom Hotel', 'Almadies Dakar', '14.695270', '-17.473630', 'fanzone_hotel']);
            fputcsv($file, ['Bar Saint-Louis', 'Place Faidherbe Saint-Louis', '16.017500', '-16.500000', 'regions']);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import bars from CSV file
     */
    public function importBars(Request $request)
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        // Mapping des types PDV (accepte plusieurs variantes)
        $typePdvMapping = [
            // CHR
            'chr' => 'chr',
            'cafés-hôtel-restaurants' => 'chr',
            'cafe-hotel-restaurant' => 'chr',
            'restaurant' => 'chr',
            'cafe' => 'chr',
            // Dakar
            'dakar' => 'dakar',
            'pdv dakar' => 'dakar',
            'points de vente dakar' => 'dakar',
            // Régions
            'regions' => 'regions',
            'région' => 'regions',
            'pdv regions' => 'regions',
            'points de vente régions' => 'regions',
            // Fanzone générique
            'fanzone' => 'fanzone',
            'fan zone' => 'fanzone',
            'fanzones' => 'fanzone',
            // Fanzone tout public
            'fanzone_public' => 'fanzone_public',
            'fanzone tout public' => 'fanzone_public',
            'fanzone public' => 'fanzone_public',
            'fan zone tout public' => 'fanzone_public',
            'fan zone public' => 'fanzone_public',
            // Fanzone hôtel
            'fanzone_hotel' => 'fanzone_hotel',
            'fanzone hotel' => 'fanzone_hotel',
            'fanzone hôtel' => 'fanzone_hotel',
            'fan zone hotel' => 'fanzone_hotel',
            'fan zone hôtel' => 'fanzone_hotel',
            'hotel' => 'fanzone_hotel',
        ];

        $validTypes = array_keys(Bar::getTypePdvOptions());

        try {
            $file = $request->file('csv_file');
            $handle = fopen($file->getRealPath(), 'r');

            // Skip BOM if present
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }

            // Lire l'en-tête
            $header = fgetcsv($handle, 1000, ',');

            if (!$header || count($header) < 4) {
                fclose($handle);
                return back()->with('error', 'Format de fichier invalide. Assurez-vous que le fichier contient les colonnes : nom, adresse, latitude, longitude');
            }

            // Normaliser les noms de colonnes de l'en-tête
            $headerNormalized = array_map(function($col) {
                return strtolower(trim($col));
            }, $header);

            // Détecter l'index de la colonne type_pdv si présente
            $typePdvIndex = null;
            $typePdvColNames = ['type_pdv', 'type', 'cat', 'categorie', 'category'];
            foreach ($typePdvColNames as $colName) {
                $index = array_search($colName, $headerNormalized);
                if ($index !== false) {
                    $typePdvIndex = $index;
                    break;
                }
            }

            $imported = 0;
            $skipped = 0;
            $errors = [];
            $lineNumber = 1;

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                $lineNumber++;

                // Vérifier qu'on a au moins 4 colonnes
                if (count($data) < 4) {
                    $errors[] = "Ligne {$lineNumber} : Données incomplètes";
                    continue;
                }

                $name = trim($data[0]);
                $address = trim($data[1]);
                $latitude = trim(str_replace(',', '.', $data[2])); // Convertir virgule en point
                $longitude = trim(str_replace(',', '.', $data[3])); // Convertir virgule en point

                // Validation basique
                if (empty($name) || empty($address)) {
                    $errors[] = "Ligne {$lineNumber} : Nom ou adresse manquant";
                    continue;
                }

                // Vérifier si les coordonnées sont valides (peuvent être vides)
                $hasValidCoords = !empty($latitude) && !empty($longitude) && is_numeric($latitude) && is_numeric($longitude);

                // Vérifier les doublons (même nom et même adresse)
                $exists = Bar::where('name', $name)->where('address', $address)->exists();
                if ($exists) {
                    $skipped++;
                    continue;
                }

                // Gérer le type PDV
                $typePdv = null;
                if ($typePdvIndex !== null && isset($data[$typePdvIndex])) {
                    $rawType = strtolower(trim($data[$typePdvIndex]));
                    
                    // Chercher dans le mapping
                    if (isset($typePdvMapping[$rawType])) {
                        $typePdv = $typePdvMapping[$rawType];
                    } elseif (in_array($rawType, $validTypes)) {
                        $typePdv = $rawType;
                    } else {
                        $errors[] = "Ligne {$lineNumber} : Type PDV '{$data[$typePdvIndex]}' non reconnu (valeurs acceptées: " . implode(', ', $validTypes) . ")";
                        // On continue quand même avec type null
                    }
                }

                // Créer le point de vente
                Bar::create([
                    'name' => $name,
                    'address' => $address,
                    'latitude' => $hasValidCoords ? (float) $latitude : null,
                    'longitude' => $hasValidCoords ? (float) $longitude : null,
                    'type_pdv' => $typePdv,
                    'is_active' => true, // Actif par défaut
                ]);

                $imported++;
            }

            fclose($handle);

            // Message de résultat
            $message = "{$imported} point(s) de vente importé(s) avec succès.";

            if ($skipped > 0) {
                $message .= " {$skipped} doublon(s) ignoré(s).";
            }

            if (count($errors) > 0) {
                $message .= " " . count($errors) . " erreur(s) : " . implode(', ', array_slice($errors, 0, 3));
                if (count($errors) > 3) {
                    $message .= " (et " . (count($errors) - 3) . " autre(s)...)";
                }
            }

            return redirect()->route('admin.bars')->with('success', $message);

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'importation : ' . $e->getMessage());
        }
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

    // ==================== STADIUMS ====================

    /**
     * List all stadiums
     */
    public function stadiums()
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $stadiums = Stadium::orderBy('city')->orderBy('name')->paginate(20);

        return view('admin.stadiums', compact('stadiums'));
    }

    /**
     * Show create form for a stadium
     */
    public function createStadium()
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        return view('admin.create-stadium');
    }

    /**
     * Store a new stadium
     */
    public function storeStadium(Request $request)
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'capacity' => 'required|integer|min:0',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        Stadium::create([
            'name' => $request->name,
            'city' => $request->city,
            'capacity' => $request->capacity,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.stadiums')->with('success', 'Stade créé avec succès.');
    }

    /**
     * Show edit form for a stadium
     */
    public function editStadium($id)
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $stadium = Stadium::findOrFail($id);

        return view('admin.edit-stadium', compact('stadium'));
    }

    /**
     * Update stadium details
     */
    public function updateStadium(Request $request, $id)
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $stadium = Stadium::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'capacity' => 'required|integer|min:0',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $stadium->update([
            'name' => $request->name,
            'city' => $request->city,
            'capacity' => $request->capacity,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.stadiums')->with('success', 'Stade mis à jour avec succès.');
    }

    /**
     * Delete a stadium
     */
    public function deleteStadium($id)
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $stadium = Stadium::findOrFail($id);

        // Vérifier si le stade est utilisé dans des matchs
        $matchCount = MatchGame::where('stadium', $stadium->name)->count();
        if ($matchCount > 0) {
            return back()->with('error', "Impossible de supprimer ce stade car il est utilisé dans {$matchCount} match(s).");
        }

        $stadium->delete();

        return redirect()->route('admin.stadiums')->with('success', 'Stade supprimé avec succès.');
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
     * Show predictions for a specific match
     */
    public function matchPredictions($id)
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $match = MatchGame::with(['homeTeam', 'awayTeam'])->findOrFail($id);

        $predictions = Prediction::with('user')
            ->where('match_id', $id)
            ->orderBy('created_at', 'asc')
            ->get();

        return view('admin.match-predictions', compact('match', 'predictions'));
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

    /**
     * Bulk delete predictions
     */
    public function bulkDeletePredictions(Request $request)
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $request->validate([
            'prediction_ids' => 'required|array|min:1',
            'prediction_ids.*' => 'integer|exists:predictions,id',
        ]);

        $count = Prediction::whereIn('id', $request->prediction_ids)->delete();

        return back()->with('success', "{$count} pronostic(s) supprimé(s) avec succès.");
    }

    /**
     * Bulk delete matches and their associated predictions
     */
    public function bulkDeleteMatches(Request $request)
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $request->validate([
            'match_ids' => 'required|array|min:1',
            'match_ids.*' => 'integer|exists:matches,id',
        ]);

        $matchIds = $request->match_ids;

        // Delete all predictions associated with these matches
        Prediction::whereIn('match_id', $matchIds)->delete();

        // Delete the matches
        $count = MatchGame::whereIn('id', $matchIds)->delete();

        return back()->with('success', "{$count} match(es) et ses pronostics supprimés avec succès.");
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

        // Récupérer ou créer les paramètres du site
        $settings = SiteSetting::with('favoriteTeam')->firstOrCreate([], [
            'site_name' => 'SOBOA Grande Fête du Foot Africain',
            'primary_color' => '#003399',
            'secondary_color' => '#FF6600',
        ]);

        // Récupérer toutes les équipes pour le select
        $teams = Team::orderBy('name')->get();

        return view('admin.settings', compact('settings', 'teams'));
    }

    /**
     * Update settings
     */
    public function updateSettings(Request $request)
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $request->validate([
            'site_name' => 'required|string|max:255',
            'primary_color' => 'required|string|max:7',
            'secondary_color' => 'required|string|max:7',
            'favorite_team_id' => 'nullable|exists:teams,id',
            'geofencing_radius' => 'required|integer|min:50|max:1000',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
        ]);

        $settings = SiteSetting::firstOrCreate([]);

        $dataToUpdate = [
            'site_name' => $request->input('site_name'),
            'primary_color' => $request->input('primary_color'),
            'secondary_color' => $request->input('secondary_color'),
            'favorite_team_id' => $request->input('favorite_team_id'),
            'geofencing_radius' => $request->input('geofencing_radius'),
        ];

        // Gérer l'upload du logo
        if ($request->hasFile('logo')) {
            // Supprimer l'ancien logo s'il existe
            if ($settings->logo_path && \Storage::disk('public')->exists($settings->logo_path)) {
                \Storage::disk('public')->delete($settings->logo_path);
            }

            // Stocker le nouveau logo
            $logoPath = $request->file('logo')->store('logos', 'public');
            $dataToUpdate['logo_path'] = $logoPath;
        }

        // Gérer la suppression du logo
        if ($request->has('remove_logo') && $request->input('remove_logo') == '1') {
            if ($settings->logo_path && \Storage::disk('public')->exists($settings->logo_path)) {
                \Storage::disk('public')->delete($settings->logo_path);
            }
            $dataToUpdate['logo_path'] = null;
        }

        $settings->update($dataToUpdate);

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

            return back()->with(
                'success',
                "Calcul terminé ! {$qualifiedCount} équipes (1ers et 2es) + {$bestThirdsCount} meilleurs 3èmes = " .
                ($qualifiedCount + $bestThirdsCount) . " équipes qualifiées pour les 1/8e."
            );

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    /**
     * Affiche les logs OTP des administrateurs
     */
    public function otpLogs(Request $request)
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $query = AdminOtpLog::orderBy('created_at', 'desc');

        // Filtrer par statut
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filtrer par numéro de téléphone
        if ($request->has('phone') && $request->phone) {
            $query->where('phone', 'like', '%' . $request->phone . '%');
        }

        $otpLogs = $query->paginate(50);

        // Statistiques
        $stats = [
            'total_sent' => AdminOtpLog::where('status', 'sent')->count(),
            'total_verified' => AdminOtpLog::where('status', 'verified')->count(),
            'total_failed' => AdminOtpLog::where('status', 'failed')->count(),
            'total_expired' => AdminOtpLog::where('status', 'expired')->count(),
        ];

        return view('admin.otp-logs', compact('otpLogs', 'stats'));
    }

    // ==================== ANIMATIONS (Venue-Match Links) ====================

    /**
     * List all animations
     */
    public function animations(Request $request)
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $query = Animation::with(['bar', 'match.homeTeam', 'match.awayTeam']);

        // Filter by bar
        if ($request->filled('bar_id')) {
            $query->where('bar_id', $request->bar_id);
        }

        // Filter by match
        if ($request->filled('match_id')) {
            $query->where('match_id', $request->match_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('animation_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('animation_date', '<=', $request->date_to);
        }

        $animations = $query->orderBy('animation_date', 'desc')->paginate(50);

        // Get all bars and matches for filters
        $bars = Bar::orderBy('name')->get();
        $matches = MatchGame::with(['homeTeam', 'awayTeam'])->orderBy('match_date', 'desc')->get();

        // Statistics
        $stats = [
            'total' => Animation::count(),
            'active' => Animation::where('is_active', true)->count(),
            'upcoming' => Animation::where('animation_date', '>', now())->count(),
            'past' => Animation::where('animation_date', '<=', now())->count(),
        ];

        return view('admin.animations', compact('animations', 'bars', 'matches', 'stats'));
    }

    /**
     * Show create form for an animation
     */
    public function createAnimation()
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $bars = Bar::where('is_active', true)->orderBy('name')->get();
        $teams = \App\Models\Team::orderBy('name')->get();

        return view('admin.create-animation', compact('bars', 'teams'));
    }

    /**
     * Store a new animation
     */
    public function storeAnimation(Request $request)
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $request->validate([
            'bar_id' => 'required|exists:bars,id',
            'home_team_id' => 'required|exists:teams,id',
            'away_team_id' => 'required|exists:teams,id',
            'animation_date' => 'required|date',
            'animation_time' => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'phase' => 'nullable|string|max:50',
        ]);

        // Validate that home and away teams are different
        if ($request->home_team_id == $request->away_team_id) {
            return back()->with('error', 'Les équipes à domicile et extérieure doivent être différentes.')
                ->withInput();
        }

        // Find or create the match with these two teams
        $match = MatchGame::where(function ($query) use ($request) {
            $query->where('home_team_id', $request->home_team_id)
                ->where('away_team_id', $request->away_team_id);
        })->orWhere(function ($query) use ($request) {
            $query->where('home_team_id', $request->away_team_id)
                ->where('away_team_id', $request->home_team_id);
        })->first();

        if (!$match) {
            // Create a new match if it doesn't exist
            // Fetch team names for team_a and team_b fields
            $homeTeam = \App\Models\Team::find($request->home_team_id);
            $awayTeam = \App\Models\Team::find($request->away_team_id);

            $match = MatchGame::create([
                'home_team_id' => $request->home_team_id,
                'away_team_id' => $request->away_team_id,
                'team_a' => $homeTeam ? $homeTeam->name : 'Team A',
                'team_b' => $awayTeam ? $awayTeam->name : 'Team B',
                'match_date' => $request->animation_date,
                'status' => 'scheduled',
                'phase' => $request->phase ?? 'group_stage',
            ]);
        } elseif ($request->phase) {
            $match->update(['phase' => $request->phase]);
        }

        // Check if animation already exists for this bar-match combination
        $existing = Animation::where('bar_id', $request->bar_id)
            ->where('match_id', $match->id)
            ->first();

        if ($existing) {
            return back()->with('error', 'Une animation existe déjà pour ce bar et ce match.')
                ->withInput();
        }

        Animation::create([
            'bar_id' => $request->bar_id,
            'match_id' => $match->id,
            'animation_date' => $request->animation_date,
            'animation_time' => $request->animation_time,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.animations')->with('success', 'Animation créée avec succès.');
    }

    /**
     * Show edit form for an animation
     */
    public function editAnimation($id)
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $animation = Animation::with(['bar', 'match.homeTeam', 'match.awayTeam'])->findOrFail($id);
        $bars = Bar::where('is_active', true)->orderBy('name')->get();
        $teams = \App\Models\Team::orderBy('name')->get();

        return view('admin.edit-animation', compact('animation', 'bars', 'teams'));
    }

    /**
     * Update animation details
     */
    public function updateAnimation(Request $request, $id)
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $animation = Animation::findOrFail($id);

        $request->validate([
            'bar_id' => 'required|exists:bars,id',
            'home_team_id' => 'required|exists:teams,id',
            'away_team_id' => 'required|exists:teams,id',
            'animation_date' => 'required|date',
            'animation_time' => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'phase' => 'nullable|string|max:50',
        ]);

        // Validate that home and away teams are different
        if ($request->home_team_id == $request->away_team_id) {
            return back()->with('error', 'Les équipes à domicile et extérieure doivent être différentes.')
                ->withInput();
        }

        // Find or create the match with these two teams
        $match = MatchGame::where(function ($query) use ($request) {
            $query->where('home_team_id', $request->home_team_id)
                ->where('away_team_id', $request->away_team_id);
        })->orWhere(function ($query) use ($request) {
            $query->where('home_team_id', $request->away_team_id)
                ->where('away_team_id', $request->home_team_id);
        })->first();

        if (!$match) {
            // Create a new match if it doesn't exist
            // Fetch team names for team_a and team_b fields
            $homeTeam = \App\Models\Team::find($request->home_team_id);
            $awayTeam = \App\Models\Team::find($request->away_team_id);

            $match = MatchGame::create([
                'home_team_id' => $request->home_team_id,
                'away_team_id' => $request->away_team_id,
                'team_a' => $homeTeam ? $homeTeam->name : 'Team A',
                'team_b' => $awayTeam ? $awayTeam->name : 'Team B',
                'match_date' => $request->animation_date,
                'status' => 'scheduled',
                'phase' => $request->phase ?? 'group_stage',
            ]);
        } elseif ($request->phase) {
            $match->update(['phase' => $request->phase]);
        }

        // Check if another animation exists for this bar-match combination (excluding current)
        $existing = Animation::where('bar_id', $request->bar_id)
            ->where('match_id', $match->id)
            ->where('id', '!=', $id)
            ->first();

        if ($existing) {
            return back()->with('error', 'Une animation existe déjà pour ce bar et ce match.')
                ->withInput();
        }

        $animation->update([
            'bar_id' => $request->bar_id,
            'match_id' => $match->id,
            'animation_date' => $request->animation_date,
            'animation_time' => $request->animation_time,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.animations')->with('success', 'Animation mise à jour avec succès.');
    }

    /**
     * Toggle animation active status
     */
    public function toggleAnimation($id)
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $animation = Animation::findOrFail($id);
        $animation->update(['is_active' => !$animation->is_active]);

        $status = $animation->is_active ? 'activée' : 'désactivée';
        return back()->with('success', "Animation {$status} avec succès.");
    }

    /**
     * Delete an animation
     */
    public function deleteAnimation($id)
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $animation = Animation::findOrFail($id);
        $animation->delete();

        return redirect()->route('admin.animations')->with('success', 'Animation supprimée avec succès.');
    }

    /**
     * View animations for a specific bar
     */
    public function barAnimations($barId)
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        $bar = Bar::findOrFail($barId);
        $animations = Animation::with(['match.homeTeam', 'match.awayTeam'])
            ->where('bar_id', $barId)
            ->orderBy('animation_date', 'desc')
            ->get();

        return view('admin.bar-animations', compact('bar', 'animations'));
    }

    /**
     * Get venues for a match (AJAX)
     */
    public function getMatchVenues($matchId)
    {
        if (!$this->checkAdmin()) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé.'], 403);
        }

        $match = MatchGame::with('animations')->findOrFail($matchId);
        $venues = Bar::where('is_active', true)->orderBy('zone')->orderBy('name')->get();
        $assignedVenueIds = $match->animations->pluck('bar_id')->toArray();

        return response()->json([
            'success' => true,
            'match' => [
                'id' => $match->id,
                'team_a' => $match->team_a,
                'team_b' => $match->team_b,
                'match_date' => $match->match_date->format('d/m/Y H:i'),
            ],
            'venues' => $venues->map(function ($venue) {
                return [
                    'id' => $venue->id,
                    'name' => $venue->name,
                    'zone' => $venue->zone,
                ];
            }),
            'assignedVenueIds' => $assignedVenueIds,
        ]);
    }

    /**
     * Assign a venue to a match (AJAX)
     */
    public function assignVenueToMatch($matchId, $venueId)
    {
        if (!$this->checkAdmin()) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé.'], 403);
        }

        $match = MatchGame::findOrFail($matchId);
        $venue = Bar::findOrFail($venueId);

        // Check if already assigned
        $existing = Animation::where('bar_id', $venueId)
            ->where('match_id', $matchId)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Ce lieu est déjà assigné à ce match.',
            ]);
        }

        // Create animation
        Animation::create([
            'bar_id' => $venueId,
            'match_id' => $matchId,
            'animation_date' => $match->match_date->format('Y-m-d'),
            'animation_time' => $match->match_date->format('H:i:s'),
            'is_active' => true,
        ]);

        $venueCount = Animation::where('match_id', $matchId)->count();

        return response()->json([
            'success' => true,
            'message' => 'Lieu assigné avec succès.',
            'venueCount' => $venueCount,
        ]);
    }

    /**
     * Unassign a venue from a match (AJAX)
     */
    public function unassignVenueFromMatch($matchId, $venueId)
    {
        if (!$this->checkAdmin()) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé.'], 403);
        }

        $animation = Animation::where('bar_id', $venueId)
            ->where('match_id', $matchId)
            ->first();

        if (!$animation) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune assignation trouvée.',
            ]);
        }

        $animation->delete();

        $venueCount = Animation::where('match_id', $matchId)->count();

        return response()->json([
            'success' => true,
            'message' => 'Lieu désassigné avec succès.',
            'venueCount' => $venueCount,
        ]);
    }

    /**
     * Clear all application cache
     */
    public function clearCache()
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        try {
            \Artisan::call('cache:clear');
            \Artisan::call('config:clear');
            \Artisan::call('view:clear');
            \Artisan::call('route:clear');

            return redirect()->back()->with('success', 'Cache vidé avec succès !');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors du vidage du cache : ' . $e->getMessage());
        }
    }

    /**
     * Match calendar view
     */
    public function calendar(Request $request)
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        // Get current month/year or from request
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        // Create date object
        $date = \Carbon\Carbon::create($year, $month, 1);

        // Get matches for the selected month grouped by date
        $matches = MatchGame::with(['homeTeam', 'awayTeam', 'animations.bar'])
            ->whereYear('match_date', $year)
            ->whereMonth('match_date', $month)
            ->orderBy('match_date')
            ->get()
            ->groupBy(function ($match) {
                return $match->match_date->format('Y-m-d');
            });

        // Get navigation dates
        $prevMonth = $date->copy()->subMonth();
        $nextMonth = $date->copy()->addMonth();

        return view('admin.calendar', compact('matches', 'date', 'prevMonth', 'nextMonth'));
    }

    /**
     * Match-Venue Matrix view
     */
    public function matchVenueMatrix(Request $request)
    {
        if (!$this->checkAdmin()) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        // Get filters
        $phase = $request->input('phase');
        $zone = $request->input('zone');

        // Build matches query
        $matchesQuery = MatchGame::with(['homeTeam', 'awayTeam', 'animations'])
            ->orderBy('match_date');

        if ($phase) {
            $matchesQuery->where('phase', $phase);
        }

        $matches = $matchesQuery->get();

        // Build bars query
        $barsQuery = Bar::where('is_active', true)->orderBy('zone')->orderBy('name');

        if ($zone) {
            $barsQuery->where('zone', $zone);
        }

        $bars = $barsQuery->get();

        // Create matrix: [match_id][bar_id] = animation or null
        $matrix = [];
        foreach ($matches as $match) {
            $matrix[$match->id] = [];
            foreach ($match->animations as $animation) {
                $matrix[$match->id][$animation->bar_id] = $animation;
            }
        }

        // Get unique zones and phases for filters
        $zones = Bar::whereNotNull('zone')
            ->distinct()
            ->orderBy('zone')
            ->pluck('zone');

        $phases = [
            'group_stage' => 'Phase de Poules',
            'round_of_16' => 'Huitièmes de finale',
            'quarter_final' => 'Quarts de finale',
            'semi_final' => 'Demi-finales',
            'third_place' => '3ème place',
            'final' => 'Finale',
        ];

        return view('admin.match-venue-matrix', compact('matches', 'bars', 'matrix', 'zones', 'phases', 'phase', 'zone'));
    }
}
