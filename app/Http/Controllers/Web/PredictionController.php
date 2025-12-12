<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Bar;
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
            'venue_id' => 'required|exists:bars,id',
        ]);

        // Vérifier que le point de vente est valide et actif
        $venue = Bar::where('id', $request->venue_id)->where('is_active', true)->first();
        
        if (!$venue) {
            return redirect()->route('venues')->with('error', 'Veuillez sélectionner un point de vente valide.');
        }

        // Vérifier que le point de vente en session correspond
        if (session('selected_venue_id') != $request->venue_id) {
            return redirect()->route('venues')->with('error', 'Veuillez vérifier votre position au point de vente.');
        }

        $match = MatchGame::findOrFail($request->match_id);
        $userId = session('user_id');

        // Vérifier que le match n'a pas encore commencé
        if ($match->status === 'finished') {
            return back()->with('error', 'Ce match est déjà terminé.');
        }

        if ($match->status === 'live') {
            return back()->with('error', 'Ce match est en cours. Les pronostics sont fermés.');
        }

        // Lock predictions 1 hour before match starts
        $lockTime = $match->match_date->copy()->subHour();
        if (now()->gte($lockTime)) {
            return back()->with('error', 'Les pronostics sont fermés 1 heure avant le match.');
        }

        // Déterminer le gagnant prédit
        $predictedWinner = 'draw';
        if ($request->score_a > $request->score_b) {
            $predictedWinner = 'home';
        } elseif ($request->score_b > $request->score_a) {
            $predictedWinner = 'away';
        }

        // Vérifier si l'utilisateur a déjà pronostiqué sur ce match
        $existingPrediction = Prediction::where('user_id', $userId)
            ->where('match_id', $request->match_id)
            ->first();

        if ($existingPrediction) {
            // Mettre à jour le pronostic existant
            $existingPrediction->update([
                'predicted_winner' => $predictedWinner,
                'score_a' => $request->score_a,
                'score_b' => $request->score_b,
            ]);

            return back()->with('toast', json_encode([
                'type' => 'success',
                'message' => 'Pronostic modifié ! ✏️',
                'description' => $match->team_a . ' ' . $request->score_a . ' - ' . $request->score_b . ' ' . $match->team_b . ' (depuis ' . $venue->name . ') • +1 pt participation garanti + jusqu\'à 6 pts bonus si exact !'
            ]));
        }

        // Créer un nouveau pronostic
        Prediction::create([
            'user_id' => $userId,
            'match_id' => $request->match_id,
            'predicted_winner' => $predictedWinner,
            'score_a' => $request->score_a,
            'score_b' => $request->score_b,
        ]);

        return back()->with('toast', json_encode([
            'type' => 'success',
            'message' => 'Pronostic enregistré ! 🎯',
            'description' => $match->team_a . ' ' . $request->score_a . ' - ' . $request->score_b . ' ' . $match->team_b . ' (depuis ' . $venue->name . ') • +1 pt participation garanti + jusqu\'à 6 pts bonus si exact !'
        ]));
    }

    public function myPredictions()
    {
        if (!session('user_id')) {
            return redirect()->route('login')->with('error', 'Vous devez être connecté.');
        }

        $userId = session('user_id');
        $user = User::find($userId);

        // Récupérer toutes les prédictions avec leurs matchs
        $allPredictions = Prediction::with('match')
            ->where('user_id', $userId)
            ->get();

        // Grouper par statut du match
        $livePredictions = $allPredictions->filter(function ($prediction) {
            return $prediction->match && $prediction->match->status === 'live';
        })->sortByDesc('match.match_date');

        $scheduledPredictions = $allPredictions->filter(function ($prediction) {
            return $prediction->match && $prediction->match->status === 'scheduled';
        })->sortByDesc('match.match_date');

        $finishedPredictions = $allPredictions->filter(function ($prediction) {
            return $prediction->match && $prediction->match->status === 'finished';
        })->sortByDesc('match.match_date');

        // Statistiques
        $totalPredictions = $allPredictions->count();
        $successfulPredictions = $finishedPredictions->where('points_earned', '>', 0)->count();
        $totalPointsEarned = $allPredictions->sum('points_earned');

        return view('predictions', compact(
            'livePredictions',
            'scheduledPredictions',
            'finishedPredictions',
            'totalPredictions',
            'successfulPredictions',
            'totalPointsEarned',
            'user'
        ));
    }
}
