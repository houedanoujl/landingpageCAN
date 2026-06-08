<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArchivedRanking extends Model
{
    protected $fillable = [
        'batch',
        'archived_at',
        'user_id',
        'name',
        'phone',
        'email',
        'points_total',
        'rank',
        'predictions_count',
    ];

    protected $casts = [
        'archived_at' => 'datetime',
        'points_total' => 'integer',
        'rank' => 'integer',
        'predictions_count' => 'integer',
    ];
}
