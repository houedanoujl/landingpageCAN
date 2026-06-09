<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Bar;
use App\Models\MatchComment;
use App\Models\MatchGame;
use App\Models\Prediction;
use App\Models\PredictionComment;
use App\Models\PredictionLike;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\ContentModerationService;
use App\Services\GeolocationService;
use App\Services\WhatsAppService;
use App\Services\PointsService;
use Illuminate\Http\Request;

class PredictionController extends Controller
{
    public function __construct(
        protected WhatsAppService $whatsAppService,
        protected PointsService $pointsService,
        protected GeolocationService $geolocationService
    ) {
    }

    public function store(Request $request)
    {
        // Vérifier si le tournoi est terminé
        $settings = SiteSetting::first();
        if ($settings && $settings->tournament_ended) {
            if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json(['message' => 'Le tournoi est terminé. Les pronostics sont fermés.'], 422);
            }
            return back()->with('error', 'Le tournoi est terminé. Les pronostics sont fermés.');
        }

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
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'predict_draw' => 'nullable',
            'penalty_winner' => 'nullable|in:home,away',
        ]);

        // Check if venue geofencing is required
        $requireVenue = config('game.require_venue_geofencing', false);
        $venue = null;
        // Le bonus venue n'est accordé que si la proximité est vérifiée côté serveur.
        // On ne fait jamais confiance au seul venue_id envoyé par le client.
        $venueVerified = false;

        if ($request->venue_id) {
            // User provided a venue - validate it
            $venue = Bar::where('id', $request->venue_id)->where('is_active', true)->first();

            if (!$venue) {
                if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                    return response()->json(['message' => 'Le point de vente sélectionné n\'est pas valide.'], 422);
                }
                return back()->with('error', 'Le point de vente sélectionné n\'est pas valide.');
            }

            // Vérification serveur de la proximité : les coordonnées du client doivent
            // tomber dans le rayon configuré autour du point de vente déclaré.
            $venueVerified = $this->isVenueProximityVerified($request, $venue);

            if (!$venueVerified && $requireVenue) {
                $message = 'Vous devez faire un check-in au point de vente (être sur place) avant de pronostiquer.';
                if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                    return response()->json(['message' => $message], 422);
                }
                return back()->with('error', $message);
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

        // Verrouiller les pronostics au début du match
        $lockTime = $match->match_date->copy();
        if (now()->gte($lockTime)) {
            if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json(['message' => 'Les pronostics sont fermés au début du match.'], 422);
            }
            return back()->with('error', 'Les pronostics sont fermés au début du match.');
        }

        // Déterminer le gagnant prédit
        $predictedWinner = 'draw';
        // Convertir predict_draw en boolean (peut être string '0'/'1' depuis le formulaire)
        $predictDraw = filter_var($request->predict_draw, FILTER_VALIDATE_BOOLEAN);
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

            // Award the +1 participation point immediately (idempotent per match)
            $this->pointsService->awardPredictionParticipationPoints($user, $match->id);

            // Award bonus points if prediction made from a venue (optional)
            $venuePointsAwarded = 0;
            if ($venue && $venueVerified) {
                $venuePointsAwarded = $this->pointsService->awardPredictionVenuePoints($user, $match->id, $venue->id);
            }

            // Refresh user to get updated points_total
            $user->refresh();

            // Update session with new points
            session(['user_points' => $user->points_total]);

            $successMessage = 'Pronostic modifié avec succès ! ✏️';

            // Pas de WhatsApp pour les modifications (seulement pour les nouveaux pronostics)

            if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'message' => $successMessage,
                    'success' => true,
                    'teams' => $match->team_a . ' ' . $request->score_a . ' - ' . $request->score_b . ' ' . $match->team_b,
                    'match_id' => $match->id,
                    'score_a' => (int) $request->score_a,
                    'score_b' => (int) $request->score_b,
                    'trend' => $this->matchTrend($match),
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

        // Award the +1 participation point immediately (idempotent per match)
        $this->pointsService->awardPredictionParticipationPoints($user, $match->id);

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

        // WhatsApp désactivé - plus de notifications pour les pronostics

        if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'message' => $successMessage,
                'success' => true,
                'teams' => $match->team_a . ' ' . $request->score_a . ' - ' . $request->score_b . ' ' . $match->team_b,
                'match_id' => $match->id,
                'score_a' => (int) $request->score_a,
                'score_b' => (int) $request->score_b,
                'trend' => $this->matchTrend($match),
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

    /**
     * Règle des +4 points venue : l'utilisateur doit avoir fait un CHECK-IN du
     * jour pour ce point de vente (via /check-in sur la map ou /api/venue/select,
     * qui vérifient et stockent la position en session), PUIS soumettre un
     * pronostic. Un venue_id envoyé seul ne suffit jamais (anti-triche).
     *
     * Conditions cumulatives :
     *  1. Check-in du jour en session pour CE point de vente.
     *  2. Proximité GPS revérifiée côté serveur (Haversine, rayon config) :
     *     coordonnées envoyées avec le pronostic, sinon celles du check-in.
     */
    private function isVenueProximityVerified(Request $request, Bar $venue): bool
    {
        // 1. Check-in du jour requis pour ce point de vente.
        if ((int) session('selected_venue_id') !== (int) $venue->id) {
            return false;
        }

        $verifiedAt = session('venue_verified_at');
        if (!$verifiedAt || !\Illuminate\Support\Carbon::parse($verifiedAt)->isToday()) {
            return false;
        }

        // 2. Proximité revérifiée serveur : coordonnées de la requête en priorité,
        //    sinon celles enregistrées lors du check-in.
        if ($request->filled('latitude') && $request->filled('longitude')) {
            return $this->geolocationService->isWithinRadius(
                (float) $request->latitude,
                (float) $request->longitude,
                $venue
            );
        }

        $lat = session('user_latitude');
        $lng = session('user_longitude');

        if ($lat !== null && $lng !== null) {
            return $this->geolocationService->isWithinRadius((float) $lat, (float) $lng, $venue);
        }

        return false;
    }

    /**
     * Tendance agrégée d'un match : pourcentages home / nul / away.
     */
    private function matchTrend(MatchGame $match): array
    {
        // Agrégation en SQL (GROUP BY) plutôt que de charger toutes les prédictions en PHP.
        $counts = $match->predictions()
            ->selectRaw('predicted_winner, COUNT(*) as c')
            ->groupBy('predicted_winner')
            ->pluck('c', 'predicted_winner');

        $home  = (int) ($counts['home'] ?? 0);
        $draw  = (int) ($counts['draw'] ?? 0);
        $away  = (int) ($counts['away'] ?? 0);
        $total = $home + $draw + $away;

        $pct = fn ($n) => $total > 0 ? (int) round($n / $total * 100) : 0;

        return [
            'total' => $total,
            'home'  => $pct($home),
            'draw'  => $pct($draw),
            'away'  => $pct($away),
        ];
    }

    /**
     * Mur de commentaires public d'un match (feed type fil d'actualité).
     */
    public function matchWall(MatchGame $match)
    {
        $userId = session('user_id');

        $comments = $match->comments()
            ->with('user:id,name')
            ->get()
            ->map(fn ($c) => [
                'id'         => $c->id,
                'user_name'  => $c->user->name ?? 'Anonyme',
                'body'       => $c->body,
                'created_at' => $c->created_at->diffForHumans(),
                'is_mine'    => $userId ? ((int) $c->user_id === (int) $userId) : false,
            ])
            ->values();

        return response()->json([
            'match'    => $match->display_label,
            'count'    => $comments->count(),
            'auth'     => (bool) $userId,
            'comments' => $comments,
        ]);
    }

    public function storeMatchComment(Request $request, MatchGame $match)
    {
        if (!session('user_id')) {
            return response()->json(['message' => 'Non connecté'], 401);
        }

        $request->validate(['body' => 'required|string|min:1|max:500']);

        $body = strip_tags($request->body);

        // Modération : liste noire de mots interdits.
        $moderation = app(ContentModerationService::class);
        $held = false;
        if (!$moderation->isClean($body)) {
            if (config('moderation.action', 'reject') === 'reject') {
                return response()->json(['message' => config('moderation.message')], 422);
            }
            $held = true; // mise en attente de modération
        }

        $userId = session('user_id');
        $comment = MatchComment::create([
            'match_id'     => $match->id,
            'user_id'      => $userId,
            'body'         => $body,
            'is_moderated' => $held,
        ]);

        if ($held) {
            return response()->json([
                'held'    => true,
                'message' => 'Votre commentaire sera publié après validation par un modérateur.',
            ], 202);
        }

        return response()->json([
            'id'         => $comment->id,
            'user_name'  => User::find($userId)->name,
            'body'       => $comment->body,
            'created_at' => $comment->created_at->diffForHumans(),
            'is_mine'    => true,
            'count'      => $match->comments()->count(),
        ], 201);
    }

    public function destroyMatchComment(Request $request, MatchGame $match, MatchComment $comment)
    {
        $userId = session('user_id');
        $user = User::find($userId);

        if (!$userId) {
            return response()->json(['message' => 'Non connecté'], 401);
        }

        if ($comment->user_id !== (int) $userId && !($user->is_admin ?? false)) {
            return response()->json(['message' => 'Interdit'], 403);
        }

        $comment->delete();

        return response()->json(['count' => $match->comments()->count()]);
    }

    public function toggleLike(Request $request, Prediction $prediction)
    {
        if (!session('user_id')) {
            return response()->json(['message' => 'Non connecté'], 401);
        }

        $userId = session('user_id');
        $existing = PredictionLike::where('user_id', $userId)
            ->where('prediction_id', $prediction->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            PredictionLike::create(['user_id' => $userId, 'prediction_id' => $prediction->id]);
            $liked = true;
        }

        return response()->json([
            'liked' => $liked,
            'count' => $prediction->likes()->count(),
        ]);
    }

    public function storeComment(Request $request, Prediction $prediction)
    {
        if (!session('user_id')) {
            return response()->json(['message' => 'Non connecté'], 401);
        }

        $request->validate(['body' => 'required|string|min:1|max:500']);

        $body = strip_tags($request->body);

        // Modération : liste noire de mots interdits.
        if (!app(ContentModerationService::class)->isClean($body)
            && config('moderation.action', 'reject') === 'reject') {
            return response()->json(['message' => config('moderation.message')], 422);
        }

        $comment = PredictionComment::create([
            'user_id'       => session('user_id'),
            'prediction_id' => $prediction->id,
            'body'          => $body,
        ]);

        return response()->json([
            'id'         => $comment->id,
            'body'       => $comment->body,
            'user_name'  => User::find(session('user_id'))->name,
            'created_at' => $comment->created_at->diffForHumans(),
            'count'      => $prediction->comments()->count(),
        ], 201);
    }

    public function destroyComment(Request $request, Prediction $prediction, PredictionComment $comment)
    {
        $userId = session('user_id');
        $user = User::find($userId);

        if (!$userId) {
            return response()->json(['message' => 'Non connecté'], 401);
        }

        if ((int) $comment->user_id !== (int) $userId && !($user->is_admin ?? false)) {
            return response()->json(['message' => 'Interdit'], 403);
        }

        $comment->delete();

        return response()->json(['count' => $prediction->comments()->count()]);
    }

    public function myPredictions()
    {
        if (!session('user_id')) {
            return redirect()->route('login')->with('error', 'Vous devez être connecté.');
        }

        $userId = session('user_id');
        $user = User::find($userId);

        $allPredictions = Prediction::with(['match', 'likes', 'comments.user'])
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
