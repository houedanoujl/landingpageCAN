<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MatchComment;
use App\Models\PredictionComment;

class PredictionCommentController extends Controller
{
    public function index()
    {
        // Les commentaires signalés remontent en tête : c'est la meilleure
        // source pour découvrir les termes qui échappent à la liste noire.
        $comments = PredictionComment::with(['user', 'prediction.match'])
            ->withCount('reports')
            ->orderByDesc('reports_count')
            ->orderByDesc('created_at')
            ->paginate(30, ['*'], 'pc_page');

        $matchComments = MatchComment::with(['user', 'match'])
            ->withCount('reports')
            ->orderByDesc('reports_count')
            ->orderByDesc('created_at')
            ->paginate(30, ['*'], 'mc_page');

        return view('admin.prediction-comments.index', compact('comments', 'matchComments'));
    }

    public function destroy(PredictionComment $comment)
    {
        $comment->delete();
        return back()->with('success', 'Commentaire supprimé.');
    }

    public function moderate(PredictionComment $comment)
    {
        $comment->update(['is_moderated' => !$comment->is_moderated]);
        return back()->with('success', $comment->is_moderated ? 'Commentaire masqué.' : 'Commentaire réactivé.');
    }

    public function destroyMatchComment(MatchComment $comment)
    {
        $comment->delete();
        return back()->with('success', 'Commentaire du mur supprimé.');
    }

    public function moderateMatchComment(MatchComment $comment)
    {
        $comment->update(['is_moderated' => !$comment->is_moderated]);
        return back()->with('success', $comment->is_moderated ? 'Commentaire masqué.' : 'Commentaire réactivé.');
    }
}
