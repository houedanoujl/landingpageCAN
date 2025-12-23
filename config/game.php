<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Game Logic Configuration - GAZELLE
    |--------------------------------------------------------------------------
    |
    | These settings control the game logic for the GAZELLE prediction app.
    | Configure whether venue geofencing is required or optional.
    |
    */

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
     * Prediction lock time (minutes AFTER match starts)
     * Les pronostics sont verrouillés 20 minutes après le coup d'envoi
     */
    'prediction_lock_minutes_after_start' => 20,

    /**
     * Cache configuration for performance
     */
    'cache' => [
        'matches_ttl' => 300,        // 5 minutes
        'leaderboard_ttl' => 60,     // 1 minute
        'venues_ttl' => 3600,        // 1 hour
    ],

];
