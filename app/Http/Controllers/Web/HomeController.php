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
        // Fetch upcoming Senegal matches with team relationships
        $senegalTeam = Team::where('iso_code', 'sn')->first();
        $upcomingMatches = collect();

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

        // Fetch top 3 users for leaderboard
        $topUsers = User::orderBy('points_total', 'desc')->take(3)->get();

        // Count venues for stats
        $venueCount = Bar::where('is_active', true)->count();

        return view('welcome', compact('upcomingMatches', 'topUsers', 'venueCount'));
    }

    public function venues()
    {
        $venues = Bar::where('is_active', true)->orderBy('name')->get();
        $settings = SiteSetting::first();
        $geofencingRadius = $settings->geofencing_radius ?? 200;

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

        // Geofencing check - trouver un bar actif à proximité (rayon de 200m)
        $bars = Bar::where('is_active', true)->get();

        $foundBar = null;
        foreach ($bars as $bar) {
            $distance = $this->calculateDistance($userLat, $userLng, $bar->latitude, $bar->longitude);
            if ($distance <= 0.05) { // 50 mètres en km
                $foundBar = $bar;
                break;
            }
        }

        if ($foundBar) {
            $pointsAwarded = $pointsService->awardBarVisitPoints($user, $foundBar->id);

            // Refresh user pour obtenir les points mis à jour
            $user->refresh();

            // Mettre à jour la session avec les nouveaux points
            session(['user_points' => $user->points_total]);

            $message = $pointsAwarded > 0
                ? "Bienvenue à {$foundBar->name} ! +{$pointsAwarded} points gagnés 🎉"
                : "Bienvenue à {$foundBar->name} ! (Points déjà réclamés aujourd'hui)";

            // Envoyer notification WhatsApp si des points ont été gagnés
            if ($pointsAwarded > 0 && $user->phone) {
                try {
                    $whatsAppMessage = "🎉 Check-in réussi à {$foundBar->name}!\n\n";
                    $whatsAppMessage .= "Points gagnés: +{$pointsAwarded} pts\n";
                    $whatsAppMessage .= "Total points: {$user->points_total} pts\n\n";
                    $whatsAppMessage .= "Continuez à parier et à visiter nos lieux partenaires pour gagner plus de points!";

                    $whatsAppService->sendMessage($user->phone, $whatsAppMessage);
                } catch (\Exception $e) {
                    // Log l'erreur mais ne bloque pas le check-in
                    \Log::error('Erreur envoi WhatsApp check-in: ' . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'points_awarded' => $pointsAwarded,
                'total_points' => $user->points_total,
                'bar_name' => $foundBar->name
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Aucun lieu partenaire à proximité (moins de 200m).'
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
