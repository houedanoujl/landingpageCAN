<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PredictionComment extends Model
{
    protected $fillable = ['user_id', 'prediction_id', 'body', 'is_moderated'];

    protected $casts = [
        'is_moderated' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function prediction()
    {
        return $this->belongsTo(Prediction::class);
    }

    public function likes()
    {
        return $this->morphMany(CommentLike::class, 'comment');
    }

    public function reports()
    {
        return $this->morphMany(CommentReport::class, 'comment');
    }
}
