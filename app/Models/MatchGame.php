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
        'team_a',
        'team_b',
        'match_date',
        'stadium',
        'group_name',
        'phase',
        'status',
        'score_a',
        'score_b',
    ];

    protected $casts = [
        'match_date' => 'datetime',
    ];

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
}
