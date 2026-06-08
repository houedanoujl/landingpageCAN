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
        'hero_promo_text',
        'favorite_team_id',
        'geofencing_radius',
        'test_mode',
        'tournament_ended',
        'tournament_winner_team_id',
    ];

    protected $casts = [
        'tournament_ended' => 'boolean',
        'test_mode' => 'boolean',
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

    /**
     * Get the tournament winner team.
     */
    public function tournamentWinner()
    {
        return $this->belongsTo(Team::class, 'tournament_winner_team_id');
    }

    /**
     * Check if points attribution is enabled.
     */
    public static function isPointsEnabled(): bool
    {
        $settings = self::getSettings();
        return $settings ? !$settings->tournament_ended : true;
    }
}
