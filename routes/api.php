<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MatchController;
use App\Http\Controllers\Api\PredictionController;
use App\Http\Controllers\Api\CheckInController;
use App\Http\Controllers\Api\LeaderboardController;

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/matches', [MatchController::class, 'index']);
    Route::post('/predictions', [PredictionController::class, 'store']);
    Route::post('/check-in', [CheckInController::class, 'store']);
    Route::get('/leaderboard', [LeaderboardController::class, 'index']);
});
