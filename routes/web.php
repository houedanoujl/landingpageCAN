<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\PredictionController;
use App\Http\Controllers\Web\AuthController;

// Pages publiques
Route::get('/', [HomeController::class, 'index']);
Route::get('/matches', [HomeController::class, 'matches'])->name('matches');
Route::get('/leaderboard', [HomeController::class, 'leaderboard'])->name('leaderboard');
Route::get('/map', [HomeController::class, 'map'])->name('map');

// Authentification Firebase
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/auth/firebase-callback', [AuthController::class, 'firebaseCallback'])->name('auth.firebase-callback');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// Pronostics (requiert authentification)
Route::post('/predictions', [PredictionController::class, 'store'])->name('predictions.store');
Route::get('/mes-pronostics', [PredictionController::class, 'myPredictions'])->name('predictions.index');
