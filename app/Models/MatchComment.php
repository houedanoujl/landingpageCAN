<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatchComment extends Model
{
    protected $fillable = ['match_id', 'user_id', 'body', 'is_moderated'];

    protected $casts = [
        'is_moderated' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function match()
    {
        return $this->belongsTo(MatchGame::class, 'match_id');
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
