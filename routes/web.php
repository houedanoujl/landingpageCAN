<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\PredictionController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\AdminController;

// Pages publiques
Route::get('/', [HomeController::class, 'index']);
Route::get('/matches', [HomeController::class, 'matches'])->name('matches');
Route::get('/leaderboard', [HomeController::class, 'leaderboard'])->name('leaderboard');
Route::get('/map', [HomeController::class, 'map'])->name('map');
Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');

// Authentification Firebase
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/auth/firebase-callback', [AuthController::class, 'firebaseCallback'])->name('auth.firebase-callback');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// Pronostics (requiert authentification)
Route::post('/predictions', [PredictionController::class, 'store'])->name('predictions.store');
Route::get('/mes-pronostics', [PredictionController::class, 'myPredictions'])->name('predictions.index');

// Administration
Route::get('/admin', [AdminController::class, 'index'])->name('admin.dashboard');
Route::get('/admin/matches', [AdminController::class, 'matches'])->name('admin.matches');
Route::get('/admin/matches/{id}/edit', [AdminController::class, 'editMatch'])->name('admin.edit-match');
Route::put('/admin/matches/{id}', [AdminController::class, 'updateMatch'])->name('admin.update-match');
Route::post('/admin/matches/{id}/calculate-points', [AdminController::class, 'calculatePoints'])->name('admin.calculate-points');
Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users');
