<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bar;
use App\Services\PointsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckInController extends Controller
{
    public function store(Request $request, PointsService $pointsService)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $user = Auth::user();
        $userLat = $request->latitude;
        $userLng = $request->longitude;

        // Simple geofencing check
        // Find if user is within a certain radius (e.g., 50m) of any active bar
        $bars = Bar::where('is_active', true)->get();

        $foundBar = null;
        foreach ($bars as $bar) {
            $distance = $this->calculateDistance($userLat, $userLng, $bar->latitude, $bar->longitude);
            if ($distance <= 0.05) { // 50 meters in km
                $foundBar = $bar;
                break;
            }
        }

        if ($foundBar) {
            $pointsService->awardBarVisitPoints($user);
            return response()->json([
                'message' => 'Checked in successfully at ' . $foundBar->name,
                'points_awarded' => true // or check logs
            ]);
        }

        return response()->json(['message' => 'No bar found nearby'], 404);
    }

    /**
     * Haversine formula to calculate distance in km
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
