<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bar;
use App\Services\GeolocationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class VenueController extends Controller
{
    protected GeolocationService $geolocationService;

    public function __construct(GeolocationService $geolocationService)
    {
        $this->geolocationService = $geolocationService;
    }

    /**
     * Sélectionner un point de vente après vérification de la position.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function select(Request $request): JsonResponse
    {
        $request->validate([
            'venue_id' => 'required|exists:bars,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $venue = Bar::findOrFail($request->venue_id);
        
        if (!$venue->is_active) {
            return response()->json([
                'success' => false,
                'error' => 'Ce point de vente n\'est pas actif.',
            ], 400);
        }

        $userLat = (float) $request->latitude;
        $userLng = (float) $request->longitude;

        // Vérifier si l'utilisateur est dans le rayon du point de vente
        $isWithinRadius = $this->geolocationService->isWithinRadius($userLat, $userLng, $venue);

        if (!$isWithinRadius) {
            $distance = $this->geolocationService->calculateHaversineDistance(
                $userLat,
                $userLng,
                (float) $venue->latitude,
                (float) $venue->longitude
            );

            return response()->json([
                'success' => false,
                'error' => 'Vous êtes trop loin de ce point de vente.',
                'distance_m' => round($distance * 1000),
                'required_distance_m' => 200,
            ], 403);
        }

        // Sauvegarder dans la session
        session([
            'selected_venue_id' => $venue->id,
            'selected_venue_name' => $venue->name,
            'venue_verified_at' => now(),
            'user_latitude' => $userLat,
            'user_longitude' => $userLng,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Vous êtes bien au point de vente '{$venue->name}'!",
            'venue' => [
                'id' => $venue->id,
                'name' => $venue->name,
                'address' => $venue->address,
            ],
            'can_bet' => true,
        ]);
    }

    /**
     * Obtenir la liste de tous les points de vente actifs.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $venues = Bar::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'address', 'latitude', 'longitude']);

        return response()->json([
            'success' => true,
            'venues' => $venues,
            'geofencing_radius_m' => 200,
        ]);
    }
}
