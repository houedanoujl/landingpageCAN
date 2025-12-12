<?php

namespace App\Services;

use App\Models\Bar;

class GeolocationService
{
    /**
     * Radius in kilometers for venue proximity check.
     */
    protected float $proximityRadius = 0.2; // 200 meters

    /**
     * Check if user coordinates are within proximity of any active venue.
     *
     * @param float $userLat User's latitude
     * @param float $userLng User's longitude
     * @return Bar|null The venue if within proximity, null otherwise
     */
    public function findNearbyVenue(float $userLat, float $userLng): ?Bar
    {
        $venues = Bar::where('is_active', true)->get();

        foreach ($venues as $venue) {
            $distance = $this->calculateHaversineDistance(
                $userLat,
                $userLng,
                (float) $venue->latitude,
                (float) $venue->longitude
            );

            if ($distance <= $this->proximityRadius) {
                return $venue;
            }
        }

        return null;
    }

    /**
     * Calculate distance between two coordinates using Haversine formula.
     *
     * @param float $lat1 First latitude
     * @param float $lon1 First longitude
     * @param float $lat2 Second latitude
     * @param float $lon2 Second longitude
     * @return float Distance in kilometers
     */
    public function calculateHaversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Check if coordinates are within a specific radius of a venue.
     *
     * @param float $userLat User's latitude
     * @param float $userLng User's longitude
     * @param Bar $venue The venue to check against
     * @param float|null $radius Custom radius in km (default: 50m)
     * @return bool
     */
    public function isWithinRadius(float $userLat, float $userLng, Bar $venue, ?float $radius = null): bool
    {
        $radius = $radius ?? $this->proximityRadius;
        
        $distance = $this->calculateHaversineDistance(
            $userLat,
            $userLng,
            (float) $venue->latitude,
            (float) $venue->longitude
        );

        return $distance <= $radius;
    }

    /**
     * Get all venues with their distances from user location.
     *
     * @param float $userLat User's latitude
     * @param float $userLng User's longitude
     * @return array Array of venues with distances
     */
    public function getVenuesWithDistances(float $userLat, float $userLng): array
    {
        $venues = Bar::where('is_active', true)->get();
        $result = [];

        foreach ($venues as $venue) {
            $distance = $this->calculateHaversineDistance(
                $userLat,
                $userLng,
                (float) $venue->latitude,
                (float) $venue->longitude
            );

            $result[] = [
                'venue' => $venue,
                'distance_km' => round($distance, 3),
                'distance_m' => round($distance * 1000, 0),
                'is_nearby' => $distance <= $this->proximityRadius,
            ];
        }

        // Sort by distance
        usort($result, fn($a, $b) => $a['distance_km'] <=> $b['distance_km']);

        return $result;
    }
}
