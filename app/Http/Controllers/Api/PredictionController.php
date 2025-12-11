<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MatchGame;
use App\Models\Prediction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class PredictionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'match_id' => 'required|exists:matches,id',
            'score_a' => 'required|integer|min:0|max:20',
            'score_b' => 'required|integer|min:0|max:20',
        ]);

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
            $predictedWinner = 'team_a';
        } elseif ($request->score_b > $request->score_a) {
            $predictedWinner = 'team_b';
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

        return response()->json([
            'success' => true,
            'prediction' => $prediction,
            'message' => 'Pronostic enregistré avec succès!',
        ]);
    }
}
