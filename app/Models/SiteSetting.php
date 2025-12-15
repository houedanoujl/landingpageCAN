<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_name',
        'primary_color',
        'secondary_color',
        'logo_path',
        'hero_image_path',
        'favorite_team_id',
    ];

    /**
     * Get site settings (cached for performance)
     */
    public static function getSettings(): ?self
    {
        return Cache::remember('site_settings', 3600, function () {
            return self::first();
        });
    }

    /**
     * Clear settings cache
     */
    public static function clearCache(): void
    {
        Cache::forget('site_settings');
    }

    /**
     * Boot method to clear cache on update
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function () {
            self::clearCache();
        });
    }

    /**
     * Get logo URL
     */
    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path ? asset('storage/' . $this->logo_path) : null;
    }

    /**
     * Get hero image URL
     */
    public function getHeroImageUrlAttribute(): ?string
    {
        return $this->hero_image_path ? asset('storage/' . $this->hero_image_path) : null;
    }

    /**
     * Get the favorite team.
     */
    public function favoriteTeam()
    {
        return $this->belongsTo(Team::class, 'favorite_team_id');
    }
}
