<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class MatchGame extends Model
{
    use HasFactory;

    protected $table = 'matches';

    protected $fillable = [
        'home_team_id',
        'away_team_id',
        'team_a',
        'team_b',
        'match_date',
        'stadium',
        'group_name',
        'phase',
        'match_number',
        'bracket_position',
        'display_order',
        'parent_match_1_id',
        'parent_match_2_id',
        'winner_goes_to',
        'status',
        'score_a',
        'score_b',
    ];

    protected $casts = [
        'match_date' => 'datetime',
    ];

    /**
     * Get the home team.
     */
    public function homeTeam()
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    /**
     * Get the away team.
     */
    public function awayTeam()
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    /**
     * Get the predictions for this match.
     */
    public function predictions()
    {
        return $this->hasMany(Prediction::class, 'match_id');
    }

    /**
     * Get the current user's prediction for this match.
     */
    public function getUserPredictionAttribute()
    {
        if (!Auth::check()) {
            return null;
        }

        return $this->predictions()->where('user_id', Auth::id())->first();
    }

    /**
     * Get parent match 1 (for knockout stages).
     */
    public function parentMatch1()
    {
        return $this->belongsTo(MatchGame::class, 'parent_match_1_id');
    }

    /**
     * Get parent match 2 (for knockout stages).
     */
    public function parentMatch2()
    {
        return $this->belongsTo(MatchGame::class, 'parent_match_2_id');
    }

    /**
     * Get child matches (matches that depend on this one).
     */
    public function childMatches()
    {
        return MatchGame::where('parent_match_1_id', $this->id)
            ->orWhere('parent_match_2_id', $this->id)
            ->get();
    }

    /**
     * Get the winner team of this match.
     */
    public function getWinnerTeamIdAttribute()
    {
        if ($this->status !== 'finished' || $this->score_a === null || $this->score_b === null) {
            return null;
        }

        if ($this->score_a > $this->score_b) {
            return $this->home_team_id;
        } elseif ($this->score_b > $this->score_a) {
            return $this->away_team_id;
        }

        return null; // Draw (should handle penalties for knockout stages)
    }

    /**
     * Get the phase name in French.
     */
    public function getPhaseNameAttribute()
    {
        $phases = [
            'group_stage' => 'Phase de poules',
            'round_of_16' => '1/8e de finale',
            'quarter_final' => 'Quart de finale',
            'semi_final' => 'Demi-finale',
            'third_place' => '3e place',
            'final' => 'Finale',
        ];

        return $phases[$this->phase] ?? $this->phase;
    }
}
