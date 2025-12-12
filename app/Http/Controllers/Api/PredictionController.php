<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MatchGame;
use App\Models\Prediction;
use App\Services\GeolocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class PredictionController extends Controller
{
    protected GeolocationService $geolocationService;

    public function __construct(GeolocationService $geolocationService)
    {
        $this->geolocationService = $geolocationService;
    }

    public function store(Request $request)
    {
        $request->validate([
            'match_id' => 'required|exists:matches,id',
            'score_a' => 'required|integer|min:0|max:20',
            'score_b' => 'required|integer|min:0|max:20',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        // Vérification du geofencing - L'utilisateur doit être à moins de 50m d'un point de vente
        $userLat = (float) $request->latitude;
        $userLng = (float) $request->longitude;
        $nearbyVenue = $this->geolocationService->findNearbyVenue($userLat, $userLng);

        if (!$nearbyVenue) {
            return response()->json([
                'error' => 'Vous devez être à moins de 200 mètres d\'un point de vente pour faire un pronostic.',
                'geofencing_required' => true,
                'radius_meters' => 200,
            ], 403);
        }

        $user = Auth::user();
        $match = MatchGame::findOrFail($request->match_id);

        // Lock predictions 1 hour before match starts
        $lockTime = Carbon::parse($match->match_date)->subHour();
        
        if (Carbon::now()->gte($lockTime)) {
            return response()->json([
                'error' => 'Les pronostics sont fermés 1 heure avant le match.',
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
                'max_possible' => 7
            ],
            'venue' => [
                'id' => $nearbyVenue->id,
                'name' => $nearbyVenue->name,
            ],
        ]);
    }
}
