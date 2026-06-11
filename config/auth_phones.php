<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Numéros de téléphone autorisés pour les tests (Côte d'Ivoire)
    |--------------------------------------------------------------------------
    |
    | Ces numéros ivoiriens sont autorisés en mode test malgré la restriction
    | aux numéros sénégalais pour le grand public.
    |
    */
    'test_phones_ci' => [
        '+2250545029721',
        '+2250748348221',
    ],

    /*
    |--------------------------------------------------------------------------
    | Numéros de téléphone administrateurs
    |--------------------------------------------------------------------------
    |
    | Ces numéros ont accès à l'interface d'administration.
    | Doivent correspondre à des utilisateurs avec role='admin' dans la base.
    |
    */
    'admin_phones' => [
        '+2250748348221',
        '+2250545029721',
    ],

    /*
    |--------------------------------------------------------------------------
    | Compte SOBOA (rôle "soboa")
    |--------------------------------------------------------------------------
    |
    | Compte avec accès admin restreint (statistiques + contenu
    | « Animation SOBOA FOOT »). Créé via SoboaAdminSeeder.
    |
    */
    'soboa_admin_phone' => env('SOBOA_ADMIN_PHONE'),
    'soboa_admin_name' => env('SOBOA_ADMIN_NAME', 'Amadou (SOBOA)'),

    /*
    |--------------------------------------------------------------------------
    | Indicatifs de pays autorisés
    |--------------------------------------------------------------------------
    |
    | Par défaut, seul le Sénégal (+221) est autorisé pour les inscriptions
    | du grand public, sauf exceptions dans test_phones_ci.
    |
    */
    'allowed_country_code_public' => '+221',
    'allowed_country_code_admin' => '+225',
];
