<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'greenapi' => [
        'url' => env('GREENAPI_URL'),
        'media_url' => env('GREENAPI_MEDIA_URL'),
        'id_instance' => env('GREENAPI_ID_INSTANCE'),
        'api_token' => env('GREENAPI_API_TOKEN'),
    ],

    'twilio' => [
        'account_sid' => env('TWILIO_ACCOUNT_SID'),
        'auth_token' => env('TWILIO_AUTH_TOKEN'),
        'verify_sid' => env('TWILIO_VERIFY_SID'),
        'from_number' => env('TWILIO_FROM_NUMBER'),
        'api_key_sid' => env('TWILIO_API_KEY_SID'),
        'api_key_secret' => env('TWILIO_API_KEY_SECRET'),
    ],

    // football-data.org — used by SyncMatchScores to auto-fetch live scores.
    // Free tier: 10 calls/min. Sync command throttles to ~1 call per run and
    // only runs when there are candidate matches in the live window.
    'football_data' => [
        'enabled' => env('FOOTBALL_DATA_ENABLED', false),
        'key' => env('FOOTBALL_DATA_API_KEY'),
        'base_url' => env('FOOTBALL_DATA_BASE_URL', 'https://api.football-data.org/v4'),
        'competition' => env('FOOTBALL_DATA_COMPETITION', 'WC'), // WC = FIFA World Cup
        'timeout' => (int) env('FOOTBALL_DATA_TIMEOUT', 10),
        'cache_ttl' => (int) env('FOOTBALL_DATA_CACHE_TTL', 60), // seconds
    ],

];
