<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prediction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PredictionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'match_id' => 'required|exists:matches,id',
            'predicted_winner' => 'required|in:team_a,team_b,draw',
            'score_a' => 'required|integer|min:0',
            'score_b' => 'required|integer|min:0',
        ]);

        $user = Auth::user();

        // Check if match has already started? (optional logic)

        $prediction = Prediction::updateOrCreate(
            [
                'user_id' => $user->id,
                'match_id' => $request->match_id,
            ],
            [
                'predicted_winner' => $request->predicted_winner,
                'score_a' => $request->score_a,
                'score_b' => $request->score_b,
                // Assuming +1 point for participation is awarded immediately or kept pending until match finishes?
                // The PointsService logic seemed to handle calculations on match finish.
                // However, if we want to give +1 immediately, we could do it here.
                // But typically, we wait for results or batch processing.
                // Based on "Trigger this calculation when a Match is updated to 'finished'", I'll leave the points calculation there.
            ]
        );

        return response()->json($prediction);
    }
}
