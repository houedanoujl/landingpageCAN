<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CommentLike;
use App\Models\CommentReport;
use App\Models\MatchComment;
use App\Models\PredictionComment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentInteractionController extends Controller
{
    public function likeMatchComment(MatchComment $comment): JsonResponse
    {
        return $this->toggleLike($comment);
    }

    public function likePredictionComment(PredictionComment $comment): JsonResponse
    {
        return $this->toggleLike($comment);
    }

    public function reportMatchComment(Request $request, MatchComment $comment): JsonResponse
    {
        return $this->report($request, $comment);
    }

    public function reportPredictionComment(Request $request, PredictionComment $comment): JsonResponse
    {
        return $this->report($request, $comment);
    }

    private function toggleLike(Model $comment): JsonResponse
    {
        $userId = session('user_id');
        if (!$userId) {
            return response()->json(['message' => 'Non connecté'], 401);
        }

        $existing = CommentLike::where('user_id', $userId)
            ->where('comment_type', $comment->getMorphClass())
            ->where('comment_id', $comment->getKey())
            ->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            CommentLike::create([
                'user_id' => $userId,
                'comment_type' => $comment->getMorphClass(),
                'comment_id' => $comment->getKey(),
            ]);
            $liked = true;
        }

        return response()->json([
            'liked' => $liked,
            'count' => $comment->likes()->count(),
        ]);
    }

    /**
     * Signalement d'un commentaire : 1 signalement max par utilisateur et par
     * commentaire. Au-delà du seuil (config moderation.report_threshold), le
     * commentaire est automatiquement masqué en attente de modération humaine.
     */
    private function report(Request $request, Model $comment): JsonResponse
    {
        $userId = session('user_id');
        if (!$userId) {
            return response()->json(['message' => 'Non connecté'], 401);
        }

        $request->validate(['reason' => 'nullable|string|max:255']);

        CommentReport::firstOrCreate(
            [
                'user_id' => $userId,
                'comment_type' => $comment->getMorphClass(),
                'comment_id' => $comment->getKey(),
            ],
            ['reason' => strip_tags((string) $request->reason) ?: null]
        );

        $reportCount = $comment->reports()->count();

        // Auto-masquage au-delà du seuil : protège le mur en attendant l'admin.
        $threshold = (int) config('moderation.report_threshold', 3);
        if (!$comment->is_moderated && $reportCount >= $threshold) {
            $comment->update(['is_moderated' => true]);
        }

        return response()->json([
            'reported' => true,
            'message' => 'Merci, ce commentaire a été signalé à notre équipe de modération.',
        ]);
    }
}
