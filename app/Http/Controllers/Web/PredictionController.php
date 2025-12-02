<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MatchGame;
use App\Models\Prediction;
use App\Models\User;
use Illuminate\Http\Request;

class PredictionController extends Controller
{
    public function store(Request $request)
    {
        // Vérifier que l'utilisateur est connecté
        if (!session('user_id')) {
            return redirect()->route('login')->with('error', 'Vous devez être connecté pour faire un pronostic.');
        }

        $request->validate([
            'match_id' => 'required|exists:matches,id',
            'score_a' => 'required|integer|min:0|max:20',
            'score_b' => 'required|integer|min:0|max:20',
        ]);

        $match = MatchGame::findOrFail($request->match_id);

        // Vérifier que le match n'a pas encore commencé
        if ($match->status === 'finished') {
            return back()->with('error', 'Ce match est déjà terminé.');
        }

        if ($match->match_date <= now()) {
            return back()->with('error', 'Ce match a déjà commencé.');
        }

        $userId = session('user_id');

        // Déterminer le gagnant prédit
        $predictedWinner = 'draw';
        if ($request->score_a > $request->score_b) {
            $predictedWinner = 'team_a';
        } elseif ($request->score_b > $request->score_a) {
            $predictedWinner = 'team_b';
        }

        // Créer ou mettre à jour le pronostic
        Prediction::updateOrCreate(
            [
                'user_id' => $userId,
                'match_id' => $request->match_id,
            ],
            [
                'predicted_winner' => $predictedWinner,
                'score_a' => $request->score_a,
                'score_b' => $request->score_b,
            ]
        );

        return back()->with('success', 'Votre pronostic a été enregistré !');
    }

    public function myPredictions()
    {
        if (!session('user_id')) {
            return redirect()->route('login')->with('error', 'Vous devez être connecté.');
        }

        $userId = session('user_id');
        $user = User::find($userId);

        $predictions = Prediction::with('match')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('predictions', compact('predictions', 'user'));
    }
}
