<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GeolocationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GeolocationController extends Controller
{
    protected GeolocationService $geolocationService;

    public function __construct(GeolocationService $geolocationService)
    {
        $this->geolocationService = $geolocationService;
    }

    /**
     * Vérifier si l'utilisateur est dans une zone de point de vente.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function checkLocation(Request $request): JsonResponse
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $userLat = (float) $request->latitude;
        $userLng = (float) $request->longitude;

        $nearbyVenue = $this->geolocationService->findNearbyVenue($userLat, $userLng);

        if ($nearbyVenue) {
            return response()->json([
                'success' => true,
                'is_within_venue' => true,
                'venue' => [
                    'id' => $nearbyVenue->id,
                    'name' => $nearbyVenue->name,
                    'address' => $nearbyVenue->address,
                ],
                'can_bet' => true,
                'message' => "Vous êtes dans la zone du point de vente '{$nearbyVenue->name}'. Vous pouvez parier!",
            ]);
        }

        return response()->json([
            'success' => true,
            'is_within_venue' => false,
            'venue' => null,
            'can_bet' => false,
            'message' => "Vous devez être à moins de 200 mètres d'un point de vente pour parier.",
        ]);
    }

    /**
     * Obtenir la liste des points de vente avec leurs distances.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getNearbyVenues(Request $request): JsonResponse
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $userLat = (float) $request->latitude;
        $userLng = (float) $request->longitude;

        $venues = $this->geolocationService->getVenuesWithDistances($userLat, $userLng);

        return response()->json([
            'success' => true,
            'user_location' => [
                'latitude' => $userLat,
                'longitude' => $userLng,
            ],
            'venues' => array_map(function ($item) {
                return [
                    'id' => $item['venue']->id,
                    'name' => $item['venue']->name,
                    'address' => $item['venue']->address,
                    'latitude' => $item['venue']->latitude,
                    'longitude' => $item['venue']->longitude,
                    'distance_km' => $item['distance_km'],
                    'distance_m' => $item['distance_m'],
                    'is_nearby' => $item['is_nearby'],
                ];
            }, $venues),
            'geofencing_radius_m' => 200,
        ]);
    }
}
