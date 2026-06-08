<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Game Logic Configuration - SOBOA FOOT TIME
    |--------------------------------------------------------------------------
    |
    | These settings control the game logic for the SOBOA FOOT TIME prediction app.
    | Configure whether venue geofencing is required or optional.
    |
    */

    /**
     * Nom de la compétition affichée (données sportives).
     * La marque/plateforme reste définie par APP_NAME.
     */
    'competition_name' => env('COMPETITION_NAME', 'Football Fest 2026'),

    /**
     * Date/heure de début de la Coupe du Monde 2026 (premier match).
     * Sert de cible au décompte affiché sur la page d'accueil,
     * indépendamment des matchs test ajoutés avant cette date.
     * Heure stockée en UTC.
     */
    'world_cup_start' => env('WORLD_CUP_START', '2026-06-11 19:00:00'),

    /**
     * Require venue geofencing for predictions
     * 
     * false = Universal access (users can predict from anywhere)
     * true = Users must be at a partner venue to make predictions
     */
    'require_venue_geofencing' => env('REQUIRE_VENUE_GEOFENCING', false),

    /**
     * Bonus points awarded for making predictions from a partner venue
     * 
     * Default: 4 points per day
     */
    'venue_bonus_points' => env('VENUE_BONUS_POINTS', 4),

    /**
     * Proximity radius for venue check-in (in kilometers)
     * 
     * Default: 0.2 km (200 meters)
     */
    'venue_proximity_radius' => env('VENUE_PROXIMITY_RADIUS_KM', 0.2),

    /**
     * Points awarded for match predictions
     */
    'points' => [
        'participation' => 1,        // Points for making a prediction
        'correct_winner' => 3,       // Bonus for predicting the correct winner
        'exact_score' => 3,          // Bonus for predicting the exact score
        'venue_bonus' => env('VENUE_BONUS_POINTS', 4), // Bonus for predictions from venue
    ],

    /**
     * Maximum points possible per match
     */
    'max_points_per_match' => 7, // 1 + 3 + 3 = 7 points max

    /**
     * Prediction lock time
     * Les pronostics sont verrouillés au début du match (0 minutes)
     */
    'prediction_lock_minutes_before_start' => 0,

    /**
     * Cache configuration for performance
     */
    'cache' => [
        'matches_ttl' => 300,        // 5 minutes
        'leaderboard_ttl' => 60,     // 1 minute
        'venues_ttl' => 3600,        // 1 hour
    ],

];
