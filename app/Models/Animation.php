<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Animation extends Model
{
    use HasFactory;

    protected $fillable = [
        'bar_id',
        'match_id',
        'animation_date',
        'animation_time',
        'is_active',
    ];

    protected $casts = [
        'animation_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the bar (venue) for this animation.
     */
    public function bar()
    {
        return $this->belongsTo(Bar::class);
    }

    /**
     * Get the match for this animation.
     */
    public function match()
    {
        return $this->belongsTo(MatchGame::class, 'match_id');
    }
}
