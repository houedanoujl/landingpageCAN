<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MatchController;
use App\Http\Controllers\Api\PredictionController;
use App\Http\Controllers\Api\CheckInController;
use App\Http\Controllers\Api\LeaderboardController;
use App\Http\Controllers\Api\GeolocationController;
use App\Http\Controllers\Api\VenueController;

Route::post('/auth/login', [AuthController::class, 'login']);

// Routes de géolocalisation (accessibles sans authentification pour vérifier la position)
Route::post('/geolocation/check', [GeolocationController::class, 'checkLocation']);
Route::post('/geolocation/venues', [GeolocationController::class, 'getNearbyVenues']);

// Sélection de point de vente
Route::post('/venue/select', [VenueController::class, 'select']);

// Récupérer les matchs pour une venue
Route::get('/matches', [MatchController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/predictions', [PredictionController::class, 'store']);
    Route::post('/check-in', [CheckInController::class, 'store']);
    Route::get('/leaderboard', [LeaderboardController::class, 'index']);
});
