<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatchGame extends Model
{
    use HasFactory;

    protected $table = 'matches';

    protected $fillable = [
        'team_a',
        'team_b',
        'match_date',
        'stadium',
        'status',
        'score_a',
        'score_b',
    ];

    protected $casts = [
        'match_date' => 'datetime',
    ];
}
