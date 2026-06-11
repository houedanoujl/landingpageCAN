<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeeklyRanking extends Model
{
    protected $fillable = [
        'user_id',
        'period',
        'points',
        'rank',
        'is_winner',
    ];

    protected $casts = [
        'points' => 'integer',
        'rank' => 'integer',
        'is_winner' => 'boolean',
    ];

    /**
     * Périodes disponibles pour la Coupe du Monde 2026 (Football Fest 2026)
     * Tournoi : 11 juin → 19 juillet 2026 (USA / Canada / Mexique)
     *
     * Classements hebdomadaires: Top 15 gagnants par semaine
     * Classement spécial: Classement global sur tout le tournoi
     */
    public const PERIODS = [
        'week_1' => [
            'label' => 'Semaine 1',
            'start' => '2026-06-11', // Ouverture
            'end' => '2026-06-21',
        ],
        'week_2' => [
            'label' => 'Semaine 2',
            'start' => '2026-06-22',
            'end' => '2026-06-28',
        ],
        'week_3' => [
            'label' => 'Semaine 3',
            'start' => '2026-06-29',
            'end' => '2026-07-05',
        ],
        'week_4' => [
            'label' => 'Semaine 4',
            'start' => '2026-07-06',
            'end' => '2026-07-12',
        ],
        'week_5' => [
            'label' => 'Semaine 5',
            'start' => '2026-07-13',
            'end' => '2026-07-19',
        ],
        'semifinal' => [
            'label' => 'Spécial Finale',
            'start' => '2026-06-11', // Début du jeu
            'end' => '2026-07-19', // Finale
        ],
    ];

    /**
     * L'utilisateur associé à ce classement
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Récupère le label d'une période
     */
    public static function getPeriodLabel(string $period): string
    {
        return self::PERIODS[$period]['label'] ?? $period;
    }

    /**
     * Récupère la période actuelle basée sur la date
     */
    public static function getCurrentPeriod(): string
    {
        $now = now()->format('Y-m-d');

        foreach (self::PERIODS as $key => $period) {
            if ($key === 'semifinal') continue; // Ignorer le classement global
            
            if ($now >= $period['start'] && $now <= $period['end']) {
                return $key;
            }
        }

        // Par défaut, retourner semaine 1 si avant le tournoi ou semifinal si après
        if ($now < '2026-06-11') {
            return 'week_1';
        }

        return 'semifinal';
    }

    /**
     * Récupère les périodes disponibles (passées ou en cours)
     */
    public static function getAvailablePeriods(): array
    {
        $now = now()->format('Y-m-d');
        $available = [];

        foreach (self::PERIODS as $key => $period) {
            // Afficher une période si on est dedans ou si elle est passée
            if ($now >= $period['start'] || $key === self::getCurrentPeriod()) {
                $available[$key] = $period;
            }
        }

        return $available;
    }
}
