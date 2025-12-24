<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnimationMedia extends Model
{
    protected $table = 'animation_media';

    protected $fillable = [
        'type',
        'title',
        'description',
        'file_path',
        'thumbnail_path',
        'video_url',
        'sort_order',
        'is_active',
        'bar_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Types de médias disponibles
     */
    public const TYPE_PHOTO = 'photo';
    public const TYPE_VIDEO = 'video';

    /**
     * Le lieu associé (optionnel)
     */
    public function bar(): BelongsTo
    {
        return $this->belongsTo(Bar::class);
    }

    /**
     * Scope pour les photos actives
     */
    public function scopePhotos($query)
    {
        return $query->where('type', self::TYPE_PHOTO)
                     ->where('is_active', true)
                     ->orderBy('sort_order');
    }

    /**
     * Scope pour les vidéos actives
     */
    public function scopeVideos($query)
    {
        return $query->where('type', self::TYPE_VIDEO)
                     ->where('is_active', true)
                     ->orderBy('sort_order');
    }

    /**
     * Obtenir l'URL complète du fichier
     */
    public function getFileUrlAttribute(): string
    {
        if (str_starts_with($this->file_path, 'http')) {
            return $this->file_path;
        }
        return asset('storage/' . $this->file_path);
    }

    /**
     * Obtenir l'URL de la miniature
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        if (!$this->thumbnail_path) {
            return null;
        }
        if (str_starts_with($this->thumbnail_path, 'http')) {
            return $this->thumbnail_path;
        }
        return asset('storage/' . $this->thumbnail_path);
    }

    /**
     * Vérifier si c'est une vidéo YouTube
     */
    public function getIsYoutubeAttribute(): bool
    {
        return $this->video_url && (
            str_contains($this->video_url, 'youtube.com') ||
            str_contains($this->video_url, 'youtu.be')
        );
    }

    /**
     * Obtenir l'ID YouTube de la vidéo
     */
    public function getYoutubeIdAttribute(): ?string
    {
        if (!$this->is_youtube) {
            return null;
        }

        $patterns = [
            '/youtube\.com\/watch\?v=([^&]+)/',
            '/youtu\.be\/([^?]+)/',
            '/youtube\.com\/embed\/([^?]+)/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $this->video_url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }
}
