<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Animation;
use App\Models\Bar;
use App\Models\MatchGame;
use App\Models\PointLog;
use App\Models\Prediction;
use App\Models\SiteSetting;
use App\Models\Team;
use App\Models\User;
use App\Services\PointsService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Récupérer le lieu sélectionné par l'utilisateur (pour affichage uniquement)
        $selectedVenueId = session('selected_venue_id');
        $selectedVenue = null;

        if ($selectedVenueId) {
            $selectedVenue = Bar::find($selectedVenueId);
        }

        // Sur la page d'accueil, afficher tous les prochains matchs (y compris phases finales)
        $senegalTeam = Team::where('iso_code', 'sn')->first();

        // Afficher tous les matchs à venir dont les équipes sont définies
        $upcomingMatches = MatchGame::with(['homeTeam', 'awayTeam'])
            ->where('status', '!=', 'finished')
            ->where('match_date', '>=', now())
            ->whereNotNull('home_team_id')
            ->whereNotNull('away_team_id')
            ->orderBy('match_date', 'asc')
            ->get();

        // Récupérer le prochain match pour le hero (premier match à venir avec équipes définies)
        $nextMatch = $upcomingMatches->first();

        // Limiter à 4 matchs pour la page d'accueil
        $upcomingMatches = $upcomingMatches->take(4);

        // Fetch top 3 users for leaderboard (alphabétique en cas d'égalité)
        $topUsers = User::orderBy('points_total', 'desc')
                       ->orderBy('name', 'asc')
                       ->take(3)
                       ->get();

        // Count venues for stats
        $venueCount = Bar::where('is_active', true)->count();

        return view('welcome', compact('upcomingMatches', 'nextMatch', 'topUsers', 'venueCount', 'selectedVenue'));
    }

    public function venues()
    {
        $venues = Bar::where('is_active', true)->orderBy('name')->get();
        $settings = SiteSetting::firstOrCreate([], [
            'geofencing_radius' => 50,
        ]);
        $geofencingRadius = $settings->geofencing_radius;

        return view('venues', compact('venues', 'geofencingRadius'));
    }

    public function matches(Request $request)
    {
        // Récupérer tous les matchs futurs dont les équipes sont définies
        $allMatches = MatchGame::with(['homeTeam', 'awayTeam', 'animations.bar'])
            ->where('status', '!=', 'finished')
            ->where('match_date', '>=', now())
            ->whereNotNull('home_team_id')
            ->whereNotNull('away_team_id')
            ->orderBy('phase', 'asc')
            ->orderBy('match_date', 'asc')
            ->get();

        // Grouper les matchs par phase
        $matchesByPhase = $allMatches->groupBy('phase');

        // Pour la phase de poules, grouper aussi par groupe
        $groupStageByGroup = collect();
        if (isset($matchesByPhase['group_stage'])) {
            $groupStageByGroup = $matchesByPhase['group_stage']->groupBy('group_name')->sortKeys();
        }

        // Récupérer les pronostics de l'utilisateur connecté
        $userPredictions = [];
        if (session('user_id')) {
            $predictions = Prediction::where('user_id', session('user_id'))->get();
            foreach ($predictions as $prediction) {
                $userPredictions[$prediction->match_id] = $prediction;
            }
        }

        // Charger l'équipe favorite pour le highlighting
        $settings = SiteSetting::with('favoriteTeam')->first();
        $favoriteTeamId = $settings?->favorite_team_id;

        // Définir l'ordre et les noms des phases
        $phaseOrder = [
            'group_stage' => 'Phase de Poules',
            'round_of_16' => '1/8e de Finale',
            'quarter_final' => 'Quarts de Finale',
            'semi_final' => 'Demi-Finales',
            'third_place' => 'Match pour la 3e Place',
            'final' => 'Finale',
        ];

        return view('matches', compact('matchesByPhase', 'groupStageByGroup', 'userPredictions', 'favoriteTeamId', 'phaseOrder'));
    }

    public function leaderboard(Request $request)
    {
        $leaderboardService = app(\App\Services\LeaderboardService::class);
        
        // Récupérer la période sélectionnée (par défaut: global)
        $period = $request->get('period', 'global');
        
        // Forcer rafraîchissement du cache si demandé
        if ($request->has('refresh')) {
            \Illuminate\Support\Facades\Cache::forget("leaderboard_top15_{$period}");
            \Illuminate\Support\Facades\Cache::forget("leaderboard_top5_{$period}");
            \Illuminate\Support\Facades\Cache::forget("leaderboard_top20_{$period}");
        }
        
        // Récupérer l'ID de l'utilisateur connecté
        $userId = session('user_id');
        
        // Récupérer les données du leaderboard
        $leaderboardData = $leaderboardService->getLeaderboardData($userId, $period);
        
        return view('leaderboard', $leaderboardData);
    }

    public function map()
    {
        $venues = Bar::with(['animations.match.homeTeam', 'animations.match.awayTeam'])
            ->where('is_active', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('name')
            ->get();
        
        // Récupérer les animations groupées par date pour le calendrier
        $animations = \App\Models\Animation::with(['bar', 'match.homeTeam', 'match.awayTeam'])
            ->where('is_active', true)
            ->whereHas('match')
            ->orderBy('animation_date')
            ->orderBy('animation_time')
            ->get()
            ->groupBy(function($animation) {
                return \Carbon\Carbon::parse($animation->animation_date)->format('Y-m-d');
            });
        
        // Récupérer les médias pour les carousels (avec vérification si la table existe)
        $highlights = collect();
        $videos = collect();
        
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('animation_media')) {
                $highlights = \App\Models\AnimationMedia::photos()->get();
                $videos = \App\Models\AnimationMedia::videos()->get();
            }
        } catch (\Exception $e) {
            // Table n'existe pas encore, on garde les collections vides
        }
        
        return view('map', compact('venues', 'highlights', 'videos', 'animations'));
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

        // Points by date (last 30 days)
        $pointsByDate = PointLog::where('user_id', $userId)
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, SUM(points) as total_points, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        // Points by venue (only if bar_id column exists)
        $pointsByVenue = collect([]);
        $visitedVenues = collect([]);

        try {
            $pointsByVenue = PointLog::where('user_id', $userId)
                ->whereIn('source', ['venue_visit', 'bar_visit'])
                ->whereNotNull('bar_id')
                ->with('bar')
                ->selectRaw('bar_id, SUM(points) as total_points, COUNT(*) as visit_count')
                ->groupBy('bar_id')
                ->orderBy('total_points', 'desc')
                ->get();

            // Visited venues for map
            $visitedVenues = PointLog::where('user_id', $userId)
                ->whereIn('source', ['venue_visit', 'bar_visit'])
                ->whereNotNull('bar_id')
                ->with('bar')
                ->select('bar_id')
                ->distinct()
                ->get()
                ->pluck('bar')
                ->filter();
        } catch (\Exception $e) {
            // Column bar_id doesn't exist yet - migration not run
            // This is expected before running the migration
        }

        // Detailed activity log - last 50 actions
        $activityLog = PointLog::where('user_id', $userId)
            ->with(['bar', 'match.homeTeam', 'match.awayTeam'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return view('dashboard', compact(
            'user',
            'rank',
            'predictionCount',
            'correctPredictions',
            'venueVisits',
            'nextMatch',
            'recentPredictions',
            'pointsByDate',
            'pointsByVenue',
            'visitedVenues',
            'activityLog'
        ));
    }

    public function checkIn(Request $request, PointsService $pointsService, WhatsAppService $whatsAppService)
    {
        // Vérifier si l'utilisateur est connecté
        if (!session('user_id')) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez être connecté pour effectuer un check-in.'
            ], 401);
        }

        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $user = User::find(session('user_id'));
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non trouvé.'
            ], 404);
        }

        $userLat = $request->latitude;
        $userLng = $request->longitude;

        // Récupérer le rayon de géolocalisation depuis les paramètres (en mètres)
        $settings = SiteSetting::firstOrCreate([], [
            'geofencing_radius' => 50,
        ]);

        $geofencingRadiusMeters = $settings->geofencing_radius;
        $geofencingRadiusKm = $geofencingRadiusMeters / 1000; // Convertir en km pour le calcul

        // Geofencing check - trouver un bar actif à proximité
        $bars = Bar::where('is_active', true)->get();

        $foundBar = null;
        foreach ($bars as $bar) {
            $distance = $this->calculateDistance($userLat, $userLng, $bar->latitude, $bar->longitude);
            if ($distance <= $geofencingRadiusKm) {
                $foundBar = $bar;
                break;
            }
        }

        if ($foundBar) {
            // IMPORTANT: Les points ne sont PAS attribués ici lors du check-in depuis la map
            // Les 4 points de visite seront attribués UNIQUEMENT lors de la soumission d'un pronostic
            // via PredictionController::store() -> awardPredictionVenuePoints()

            // Stocker le bar sélectionné en session pour valider les pronostics
            session(['selected_venue_id' => $foundBar->id]);

            $message = "Lieu confirmé : {$foundBar->name} ! Vous pouvez maintenant faire vos pronostics.";

            return response()->json([
                'success' => true,
                'message' => $message,
                'points_awarded' => 0, // Pas de points lors du simple check-in
                'total_points' => $user->points_total,
                'bar_name' => $foundBar->name,
                'bar_id' => $foundBar->id
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => "Aucun lieu partenaire à proximité (moins de {$geofencingRadiusMeters}m)."
        ], 404);
    }

    /**
     * Calcul de la distance entre deux points GPS (formule de Haversine)
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Page Calendrier des Animations
     * Affiche toutes les animations avec filtrage par date et géolocalisation
     */
    public function animations(Request $request)
    {
        // Récupérer toutes les animations à venir avec les relations
        $animations = Animation::with(['bar', 'match.homeTeam', 'match.awayTeam'])
            ->whereHas('match', function($query) {
                $query->where('match_date', '>=', now()->subHours(3)); // Inclure les matchs récents
            })
            ->whereHas('bar', function($query) {
                $query->where('is_active', true);
            })
            ->orderBy('animation_date', 'asc')
            ->get();

        // Grouper par date
        $animationsByDate = $animations->groupBy(function($animation) {
            return \Carbon\Carbon::parse($animation->animation_date)->format('Y-m-d');
        });

        // Récupérer tous les PDV uniques qui ont des animations
        $venuesWithAnimations = Bar::whereHas('animations', function($query) {
            $query->whereHas('match', function($q) {
                $q->where('match_date', '>=', now()->subHours(3));
            });
        })->where('is_active', true)->orderBy('name')->get();

        // Récupérer les types de PDV uniques
        $venueTypes = $venuesWithAnimations->pluck('type_pdv')->unique()->filter()->values();

        return view('animations', compact('animations', 'animationsByDate', 'venuesWithAnimations', 'venueTypes'));
    }

    /**
     * Page Temps Forts
     * Filtre les animations par PDV spécifique
     */
    public function highlights(Request $request)
    {
        $venueId = $request->get('venue_id');
        $zone = $request->get('zone');
        $type = $request->get('type');

        // Query de base pour les animations
        $query = Animation::with(['bar', 'match.homeTeam', 'match.awayTeam'])
            ->whereHas('match', function($q) {
                $q->where('match_date', '>=', now()->subHours(3));
            })
            ->whereHas('bar', function($q) {
                $q->where('is_active', true);
            });

        // Filtrer par PDV spécifique
        if ($venueId) {
            $query->where('bar_id', $venueId);
        }

        // Filtrer par zone
        if ($zone) {
            $query->whereHas('bar', function($q) use ($zone) {
                $q->where('zone', $zone);
            });
        }

        // Filtrer par type de PDV
        if ($type) {
            $query->whereHas('bar', function($q) use ($type) {
                $q->where('type_pdv', $type);
            });
        }

        $animations = $query->orderBy('animation_date', 'asc')->get();

        // Récupérer tous les PDV pour le filtre
        $venues = Bar::whereHas('animations', function($q) {
            $q->whereHas('match', function($mq) {
                $mq->where('match_date', '>=', now()->subHours(3));
            });
        })->where('is_active', true)->orderBy('name')->get();

        // Zones uniques
        $zones = $venues->pluck('zone')->unique()->filter()->sort()->values();

        // Types de PDV uniques
        $types = $venues->pluck('type_pdv')->unique()->filter()->values();

        // Grouper les animations par date
        $animationsByDate = $animations->groupBy(function($animation) {
            return \Carbon\Carbon::parse($animation->animation_date)->format('Y-m-d');
        });

        return view('highlights', compact('animations', 'animationsByDate', 'venues', 'zones', 'types', 'venueId', 'zone', 'type'));
    }

    /**
     * Calendrier complet des matchs (passés et à venir)
     */
    public function calendar(Request $request)
    {
        // Onglet actif
        $tab = $request->input('tab', 'calendar');

        // Récupérer TOUS les matchs (passés et à venir)
        $matches = MatchGame::with(['homeTeam', 'awayTeam'])
            ->orderBy('match_date', 'desc')
            ->get();

        // Grouper par date
        $matchesByDate = $matches->groupBy(function($match) {
            return \Carbon\Carbon::parse($match->match_date)->format('Y-m-d');
        });

        // Statistiques
        $totalMatches = $matches->count();
        $finishedMatches = $matches->where('status', 'finished')->count();
        $upcomingMatches = $matches->where('status', '!=', 'finished')->count();

        // Préparer les données JSON pour Alpine.js
        $matchesJson = $matches->map(function($match) {
            return [
                'id' => $match->id,
                'date' => \Carbon\Carbon::parse($match->match_date)->format('Y-m-d'),
                'time' => \Carbon\Carbon::parse($match->match_date)->format('H:i'),
                'homeTeam' => $match->homeTeam?->name ?? $match->team_a ?? 'À déterminer',
                'awayTeam' => $match->awayTeam?->name ?? $match->team_b ?? 'À déterminer',
                'homeIso' => $match->homeTeam?->iso_code ?? null,
                'awayIso' => $match->awayTeam?->iso_code ?? null,
                'homeScore' => $match->score_a,
                'awayScore' => $match->score_b,
                'status' => $match->status,
                'stadium' => $match->stadium ?? null,
                'group' => $match->group_name,
                'phase' => $match->phase,
            ];
        })->values()->toArray();

        // ===== CLASSEMENT DES GROUPES =====
        $teams = Team::all();
        $groupMatches = MatchGame::with(['homeTeam', 'awayTeam'])
            ->where('phase', 'group_stage')
            ->where('status', 'finished')
            ->get();

        $standings = [];
        foreach ($teams as $team) {
            $played = 0;
            $wins = 0;
            $draws = 0;
            $losses = 0;
            $goalsFor = 0;
            $goalsAgainst = 0;

            foreach ($groupMatches as $match) {
                if ($match->home_team_id == $team->id) {
                    $played++;
                    $goalsFor += $match->score_a ?? 0;
                    $goalsAgainst += $match->score_b ?? 0;
                    if ($match->score_a > $match->score_b) $wins++;
                    elseif ($match->score_a == $match->score_b) $draws++;
                    else $losses++;
                } elseif ($match->away_team_id == $team->id) {
                    $played++;
                    $goalsFor += $match->score_b ?? 0;
                    $goalsAgainst += $match->score_a ?? 0;
                    if ($match->score_b > $match->score_a) $wins++;
                    elseif ($match->score_a == $match->score_b) $draws++;
                    else $losses++;
                }
            }

            $standings[] = [
                'team' => $team,
                'played' => $played,
                'wins' => $wins,
                'draws' => $draws,
                'losses' => $losses,
                'goals_for' => $goalsFor,
                'goals_against' => $goalsAgainst,
                'goal_diff' => $goalsFor - $goalsAgainst,
                'points' => ($wins * 3) + $draws,
                'group' => $team->group,
            ];
        }

        // Grouper par groupe et trier
        $groupedStandings = collect($standings)->groupBy('group')->map(function ($group) {
            return $group->sortByDesc(function ($team) {
                return [$team['points'], $team['goal_diff'], $team['goals_for']];
            })->values();
        })->sortKeys();

        // ===== PHASES FINALES (BRACKET) =====
        $knockoutMatches = MatchGame::with(['homeTeam', 'awayTeam'])
            ->whereIn('phase', ['round_of_16', 'quarter_final', 'semi_final', 'third_place', 'final'])
            ->orderBy('match_date')
            ->get()
            ->groupBy('phase');

        return view('calendar', compact(
            'matches', 'matchesByDate', 'totalMatches', 'finishedMatches', 'upcomingMatches', 'matchesJson',
            'tab', 'groupedStandings', 'knockoutMatches'
        ));
    }
}
