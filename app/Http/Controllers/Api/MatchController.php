<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MatchGame;
use Illuminate\Http\Request;

class MatchController extends Controller
{
    public function index(Request $request)
    {
        $query = MatchGame::query();

        // Filtrer par venue_id si spécifié (pour la modale des venues)
        if ($request->has('venue_id')) {
            $query->where('status', '!=', 'finished')
                  ->orderBy('match_date', 'asc');
        } else {
            $query->orderBy('match_date', 'asc');
        }

        $matches = $query->get();

        return response()->json([
            'matches' => $matches
        ]);
    }
}
