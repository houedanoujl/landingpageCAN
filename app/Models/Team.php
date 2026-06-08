<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    protected $fillable = [
        'name',
        'iso_code',
        'group',
    ];

    /**
     * Traduit un nom d'équipe (anglais en base) vers le français.
     * Retourne le nom d'origine si absent de la table de traduction.
     */
    public static function fr(?string $name): ?string
    {
        if ($name === null) {
            return null;
        }
        return config('teams_fr.' . $name, $name);
    }

    /**
     * Nom affiché en français (à utiliser dans les vues).
     */
    public function getDisplayNameAttribute(): string
    {
        return self::fr($this->name) ?? $this->name;
    }

    /**
     * Build flag URL. flagcdn.com only supports ISO 3166-1 (2 chars).
     * Subdivisions like gb-eng, gb-sct, gb-wls, gb-nir must use flagicons.lipis.dev.
     */
    protected function flagUrl(string $size): string
    {
        $iso = strtolower($this->iso_code ?? '');
        if ($iso === '' ) {
            return '';
        }
        if (str_contains($iso, '-')) {
            // Subdivision flag (e.g. gb-eng): flagicons.lipis.dev serves SVG only.
            return "https://flagicons.lipis.dev/flags/4x3/{$iso}.svg";
        }
        return "https://flagcdn.com/{$size}/{$iso}.png";
    }

    public function getFlagUrlAttribute(): string
    {
        return $this->flagUrl('w40');
    }

    public function getFlagUrl80Attribute(): string
    {
        return $this->flagUrl('w80');
    }

    /**
     * Get home matches for this team.
     */
    public function homeMatches(): HasMany
    {
        return $this->hasMany(MatchGame::class, 'home_team_id');
    }

    /**
     * Get away matches for this team.
     */
    public function awayMatches(): HasMany
    {
        return $this->hasMany(MatchGame::class, 'away_team_id');
    }
}
