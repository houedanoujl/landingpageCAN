<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Bar;
use App\Models\MatchGame;
use App\Models\Prediction;
use App\Models\User;
use App\Services\WhatsAppService;
use App\Services\PointsService;
use Illuminate\Http\Request;

class PredictionController extends Controller
{
    public function __construct(
        protected WhatsAppService $whatsAppService,
        protected PointsService $pointsService
    ) {
    }

    public function store(Request $request)
    {
        // Vérifier que l'utilisateur est connecté
        if (!session('user_id')) {
            if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json(['message' => 'Vous devez être connecté pour faire un pronostic.'], 401);
            }
            return redirect()->route('login')->with('error', 'Vous devez être connecté pour faire un pronostic.');
        }

        $request->validate([
            'match_id' => 'required|exists:matches,id',
            'score_a' => 'required|integer|min:0|max:20',
            'score_b' => 'required|integer|min:0|max:20',
            'venue_id' => 'nullable|exists:bars,id', // Venue is now optional
            'predict_draw' => 'nullable|boolean',
            'penalty_winner' => 'nullable|in:home,away',
        ]);

        // Check if venue geofencing is required
        $requireVenue = config('game.require_venue_geofencing', false);
        $venue = null;

        if ($request->venue_id) {
            // User provided a venue - validate it
            $venue = Bar::where('id', $request->venue_id)->where('is_active', true)->first();

            if (!$venue) {
                if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                    return response()->json(['message' => 'Le point de vente sélectionné n\'est pas valide.'], 422);
                }
                return back()->with('error', 'Le point de vente sélectionné n\'est pas valide.');
            }
        } elseif ($requireVenue) {
            // Venue is required but not provided
            if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json(['message' => 'Veuillez sélectionner un point de vente.'], 422);
            }
            return redirect()->route('venues')->with('error', 'Veuillez sélectionner un point de vente pour pronostiquer.');
        }

        $match = MatchGame::findOrFail($request->match_id);
        $userId = session('user_id');

        // Vérifier que le match n'a pas encore commencé
        if ($match->status === 'finished') {
            if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json(['message' => 'Ce match est déjà terminé.'], 422);
            }
            return back()->with('error', 'Ce match est déjà terminé.');
        }

        if ($match->status === 'live') {
            if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json(['message' => 'Ce match est en cours. Les pronostics sont fermés.'], 422);
            }
            return back()->with('error', 'Ce match est en cours. Les pronostics sont fermés.');
        }

        // Verrouiller les pronostics 20 minutes APRÈS le début du match
        $lockMinutesAfterStart = config('game.prediction_lock_minutes_after_start', 20);
        $lockTime = $match->match_date->copy()->addMinutes($lockMinutesAfterStart);
        if (now()->gte($lockTime)) {
            if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json(['message' => 'Les pronostics sont fermés 20 minutes après le début du match.'], 422);
            }
            return back()->with('error', 'Les pronostics sont fermés 20 minutes après le début du match.');
        }

        // Déterminer le gagnant prédit
        $predictedWinner = 'draw';
        $predictDraw = $request->predict_draw ?? false;
        $penaltyWinner = $request->penalty_winner;

        if ($request->score_a > $request->score_b) {
            $predictedWinner = 'home';
        } elseif ($request->score_b > $request->score_a) {
            $predictedWinner = 'away';
        } elseif ($predictDraw && $penaltyWinner) {
            // Égalité avec tirs au but - utiliser le vainqueur TAB
            $predictedWinner = $penaltyWinner;
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
                'predict_draw' => $predictDraw,
                'penalty_winner' => $penaltyWinner,
            ]);

            $user = User::find($userId);

            // Award bonus points if prediction made from a venue (optional)
            $venuePointsAwarded = 0;
            if ($venue) {
                $venuePointsAwarded = $this->pointsService->awardPredictionVenuePoints($user, $match->id, $venue->id);
            }

            // Refresh user to get updated points_total
            $user->refresh();

            // Update session with new points
            session(['user_points' => $user->points_total]);

            $successMessage = 'Pronostic modifié ! ✏️ ' . $match->team_a . ' ' . $request->score_a . ' - ' . $request->score_b . ' ' . $match->team_b;

            // Envoyer confirmation WhatsApp
            $whatsappResult = $this->whatsAppService->sendPredictionConfirmation($user, $match, $existingPrediction, $venue);

            if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'message' => $successMessage,
                    'whatsapp_sent' => $whatsappResult['success'] ?? false,
                    'teams' => $match->team_a . ' ' . $request->score_a . ' - ' . $request->score_b . ' ' . $match->team_b,
                    'venue' => $venue ? $venue->name : null,
                    'venue_bonus_points' => $venuePointsAwarded,
                    'user_points_total' => $user->points_total
                ], 200);
            }

            $description = $match->team_a . ' ' . $request->score_a . ' - ' . $request->score_b . ' ' . $match->team_b;
            if ($venue) {
                $description .= ' (depuis ' . $venue->name . ')';
            }
            $description .= ' • +1 pt participation garanti + jusqu\'à 6 pts bonus si exact !';
            if ($venuePointsAwarded > 0) {
                $description .= ' + ' . $venuePointsAwarded . ' pts venue bonus 🎉';
            }

            return back()->with('toast', json_encode([
                'type' => 'success',
                'message' => 'Pronostic modifié ! ✏️',
                'description' => $description
            ]));
        }

        // Créer un nouveau pronostic
        $prediction = Prediction::create([
            'user_id' => $userId,
            'match_id' => $request->match_id,
            'predicted_winner' => $predictedWinner,
            'score_a' => $request->score_a,
            'score_b' => $request->score_b,
            'predict_draw' => $predictDraw,
            'penalty_winner' => $penaltyWinner,
        ]);

        $user = User::find($userId);

        // Award bonus points ONLY if the match is being shown at this venue
        $venuePointsAwarded = 0;
        if ($venue) {
            $venuePointsAwarded = $this->pointsService->awardPredictionVenuePoints($user, $match->id, $venue->id);
        }

        // Refresh user to get updated points_total
        $user->refresh();

        // Update session with new points
        session(['user_points' => $user->points_total]);

        $successMessage = 'Pronostic enregistré ! 🎯 ' . $match->team_a . ' ' . $request->score_a . ' - ' . $request->score_b . ' ' . $match->team_b;

        // Envoyer confirmation WhatsApp
        $whatsappResult = $this->whatsAppService->sendPredictionConfirmation($user, $match, $prediction, $venue);

        if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'message' => $successMessage,
                'whatsapp_sent' => $whatsappResult['success'] ?? false,
                'teams' => $match->team_a . ' ' . $request->score_a . ' - ' . $request->score_b . ' ' . $match->team_b,
                'venue' => $venue ? $venue->name : null,
                'venue_bonus_points' => $venuePointsAwarded,
                'user_points_total' => $user->points_total
            ], 200);
        }

        $description = $match->team_a . ' ' . $request->score_a . ' - ' . $request->score_b . ' ' . $match->team_b;
        if ($venue) {
            $description .= ' (depuis ' . $venue->name . ')';
        }
        $description .= ' • +1 pt participation garanti + jusqu\'à 6 pts bonus si exact !';
        if ($venuePointsAwarded > 0) {
            $description .= ' + ' . $venuePointsAwarded . ' pts venue bonus 🎉';
        }

        return back()->with('toast', json_encode([
            'type' => 'success',
            'message' => 'Pronostic enregistré ! 🎯',
            'description' => $description
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
