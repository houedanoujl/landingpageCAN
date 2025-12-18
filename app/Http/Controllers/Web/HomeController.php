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
        $upcomingMatches = collect();

        // Récupérer le lieu sélectionné par l'utilisateur
        $selectedVenueId = session('selected_venue_id');
        $selectedVenue = null;

        if ($selectedVenueId) {
            $selectedVenue = Bar::find($selectedVenueId);
        }

        // Si un lieu est sélectionné, filtrer les matches disponibles dans ce lieu
        if ($selectedVenue) {
            // Récupérer les IDs des matches disponibles dans ce lieu via les animations
            $availableMatchIds = Animation::where('bar_id', $selectedVenue->id)
                ->where('is_active', true)
                ->pluck('match_id')
                ->toArray();

            // Récupérer les prochains matches du Sénégal disponibles dans ce lieu
            $senegalTeam = Team::where('iso_code', 'sn')->first();

            if ($senegalTeam && !empty($availableMatchIds)) {
                $upcomingMatches = MatchGame::with(['homeTeam', 'awayTeam'])
                    ->whereIn('id', $availableMatchIds)
                    ->where('status', '!=', 'finished')
                    ->where('match_date', '>=', now())
                    ->where(function ($query) use ($senegalTeam) {
                        $query->where('home_team_id', $senegalTeam->id)
                            ->orWhere('away_team_id', $senegalTeam->id);
                    })
                    ->orderBy('match_date', 'asc')
                    ->take(3)
                    ->get();
            }

            // Fallback: si pas de matches du Sénégal, afficher les prochains matches du lieu
            if ($upcomingMatches->isEmpty() && !empty($availableMatchIds)) {
                $upcomingMatches = MatchGame::with(['homeTeam', 'awayTeam'])
                    ->whereIn('id', $availableMatchIds)
                    ->where('status', '!=', 'finished')
                    ->where('match_date', '>=', now())
                    ->orderBy('match_date', 'asc')
                    ->take(3)
                    ->get();
            }
        } else {
            // Si pas de lieu sélectionné, afficher les prochains matches généraux
            $senegalTeam = Team::where('iso_code', 'sn')->first();

            if ($senegalTeam) {
                $upcomingMatches = MatchGame::with(['homeTeam', 'awayTeam'])
                    ->where('status', '!=', 'finished')
                    ->where('match_date', '>=', now())
                    ->where(function ($query) use ($senegalTeam) {
                        $query->where('home_team_id', $senegalTeam->id)
                            ->orWhere('away_team_id', $senegalTeam->id);
                    })
                    ->orderBy('match_date', 'asc')
                    ->take(3)
                    ->get();
            }

            // Fallback: if no Senegal matches, show upcoming tournament matches
            if ($upcomingMatches->isEmpty()) {
                $upcomingMatches = MatchGame::with(['homeTeam', 'awayTeam'])
                    ->where('status', '!=', 'finished')
                    ->where('match_date', '>=', now())
                    ->orderBy('match_date', 'asc')
                    ->take(3)
                    ->get();
            }
        }

        // Fetch top 3 users for leaderboard
        $topUsers = User::orderBy('points_total', 'desc')->take(3)->get();

        // Count venues for stats
        $venueCount = Bar::where('is_active', true)->count();

        return view('welcome', compact('upcomingMatches', 'topUsers', 'venueCount', 'selectedVenue'));
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

        // Récupérer uniquement les matchs assignés à ce lieu via les animations
        $animations = Animation::where('bar_id', $selectedVenue->id)
            ->where('is_active', true)
            ->with(['match.homeTeam', 'match.awayTeam'])
            ->orderBy('animation_date', 'asc')
            ->get();

        // Extraire les matchs des animations
        $venueMatches = $animations->map(function ($animation) {
            return $animation->match;
        })->unique('id');

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

        return view('matches', compact('venueMatches', 'userPredictions', 'selectedVenue', 'favoriteTeamId'));
    }

    public function leaderboard()
    {
        $users = User::orderBy('points_total', 'desc')->paginate(20);
        return view('leaderboard', compact('users'));
    }

    public function map()
    {
        $venues = Bar::with(['animations.match.homeTeam', 'animations.match.awayTeam'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
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
}
