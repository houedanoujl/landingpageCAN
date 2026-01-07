<?php
// Script temporaire pour mettre à jour le rayon de géolocalisation à 200 mètres
// À exécuter avec: php update_geofencing_radius.php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SiteSetting;

$settings = SiteSetting::first();
if ($settings) {
    $oldRadius = $settings->geofencing_radius;
    $settings->geofencing_radius = 200;
    $settings->save();
    echo "Rayon de géolocalisation mis à jour: {$oldRadius}m → 200m\n";
} else {
    SiteSetting::create(['geofencing_radius' => 200]);
    echo "Paramètres créés avec rayon de géolocalisation: 200m\n";
}
