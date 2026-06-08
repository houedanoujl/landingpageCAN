<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MatchGame;
use App\Models\Prediction;
use App\Models\SiteSetting;
use App\Services\GeolocationService;
use App\Services\PointsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class PredictionController extends Controller
{
    protected GeolocationService $geolocationService;
    protected PointsService $pointsService;

    public function __construct(GeolocationService $geolocationService, PointsService $pointsService)
    {
        $this->geolocationService = $geolocationService;
        $this->pointsService = $pointsService;
    }

    public function store(Request $request)
    {
        // Vérifier si le tournoi est terminé
        $settings = SiteSetting::first();
        if ($settings && $settings->tournament_ended) {
            return response()->json([
                'error' => 'Le tournoi est terminé. Les pronostics sont fermés.',
            ], 422);
        }

        $request->validate([
            'match_id' => 'required|exists:matches,id',
            'score_a' => 'required|integer|min:0|max:20',
            'score_b' => 'required|integer|min:0|max:20',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        // Check if venue geofencing is required
        $requireVenue = config('game.require_venue_geofencing', false);
        $nearbyVenue = null;

        // Check for nearby venue if coordinates provided
        if ($request->latitude && $request->longitude) {
            $userLat = (float) $request->latitude;
            $userLng = (float) $request->longitude;
            $nearbyVenue = $this->geolocationService->findNearbyVenue($userLat, $userLng);
        }

        // If venue is required but not found, return error
        if ($requireVenue && !$nearbyVenue) {
            return response()->json([
                'error' => 'Vous devez être à moins de 200 mètres d\'un point de vente pour faire un pronostic.',
                'geofencing_required' => true,
                'radius_meters' => 200,
            ], 403);
        }

        $user = Auth::user();
        $match = MatchGame::findOrFail($request->match_id);

        // Lock predictions at match start time
        $lockTime = Carbon::parse($match->match_date);
        
        if (Carbon::now()->gte($lockTime)) {
            return response()->json([
                'error' => 'Les pronostics sont fermés au début du match.',
                'match_date' => $match->match_date,
                'lock_time' => $lockTime,
            ], 422);
        }

        // Check if match is already finished
        if ($match->status === 'finished') {
            return response()->json([
                'error' => 'Ce match est déjà terminé.',
            ], 422);
        }

        // Derive predicted_winner from scores
        $predictedWinner = 'draw';
        if ($request->score_a > $request->score_b) {
            $predictedWinner = 'home';
        } elseif ($request->score_b > $request->score_a) {
            $predictedWinner = 'away';
        }

        $prediction = Prediction::updateOrCreate(
            [
                'user_id' => $user->id,
                'match_id' => $request->match_id,
            ],
            [
                'predicted_winner' => $predictedWinner,
                'score_a' => $request->score_a,
                'score_b' => $request->score_b,
            ]
        );

        $isNewPrediction = $prediction->wasRecentlyCreated;

        // Award the +1 participation point immediately (idempotent per match)
        $this->pointsService->awardPredictionParticipationPoints($user, $match->id);

        // Award bonus points ONLY if the match is being shown at this venue
        $venuePointsAwarded = 0;
        if ($nearbyVenue) {
            $venuePointsAwarded = $this->pointsService->awardPredictionVenuePoints($user, $match->id, $nearbyVenue->id);
        }

        // Refresh user to get updated points_total
        $user->refresh();

        // Update session with new points
        session(['user_points' => $user->points_total]);

        return response()->json([
            'success' => true,
            'prediction' => $prediction,
            'message' => $isNewPrediction
                ? 'Pronostic enregistré ! 🎯 +1 pt participation garanti + jusqu\'à 6 pts bonus si exact !'
                : 'Pronostic modifié ! ✏️ +1 pt participation garanti + jusqu\'à 6 pts bonus si exact !',
            'points_info' => [
                'participation' => 1,
                'correct_winner' => 3,
                'exact_score' => 3,
                'max_possible' => 7,
                'venue_bonus' => $venuePointsAwarded
            ],
            'user_points_total' => $user->points_total,
            'venue' => [
                'id' => $nearbyVenue->id,
                'name' => $nearbyVenue->name,
            ],
        ]);
    }
}
