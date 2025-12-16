<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\PredictionController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Web\AdminAuthController;

// Pages publiques
Route::get('/', [HomeController::class, 'index']);
Route::get('/venues', [HomeController::class, 'venues'])->name('venues');
Route::get('/matches', [HomeController::class, 'matches'])->name('matches');
Route::get('/leaderboard', [HomeController::class, 'leaderboard'])->name('leaderboard');
Route::get('/map', [HomeController::class, 'map'])->name('map');
Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');
Route::get('/conditions', function () {
    return view('terms');
})->name('terms');

// Authentification publique (Sénégal + exceptions test CI)
// Rate limiting: 5 tentatives par minute pour l'envoi d'OTP
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/auth/send-otp', [AuthController::class, 'sendOtp'])
    ->middleware('throttle:5,1')
    ->name('auth.send-otp');
Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp'])
    ->middleware('throttle:10,1')
    ->name('auth.verify-otp');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// Authentification administrateur (séparée)
// Rate limiting plus strict pour l'admin: 3 tentatives par minute
Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/auth/send-otp', [AdminAuthController::class, 'sendOtp'])
    ->middleware('throttle:3,1')
    ->name('admin.auth.send-otp');
Route::post('/admin/auth/verify-otp', [AdminAuthController::class, 'verifyOtp'])
    ->middleware('throttle:5,1')
    ->name('admin.auth.verify-otp');
Route::get('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

// Pronostics (requiert authentification)
Route::post('/predictions', [PredictionController::class, 'store'])->name('predictions.store');
Route::get('/mes-pronostics', [PredictionController::class, 'myPredictions'])->name('predictions.index');

// Check-in (requiert authentification)
Route::post('/check-in', [HomeController::class, 'checkIn'])->name('check-in');

// Administration (protégé par middleware check.admin)
Route::prefix('admin')->name('admin.')->middleware('check.admin')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');
    
    // Matchs
    Route::get('/matches', [AdminController::class, 'matches'])->name('matches');
    Route::get('/matches/create', [AdminController::class, 'createMatch'])->name('create-match');
    Route::post('/matches', [AdminController::class, 'storeMatch'])->name('store-match');
    Route::get('/matches/{id}/edit', [AdminController::class, 'editMatch'])->name('edit-match');
    Route::put('/matches/{id}', [AdminController::class, 'updateMatch'])->name('update-match');
    Route::delete('/matches/{id}', [AdminController::class, 'deleteMatch'])->name('delete-match');
    Route::post('/matches/{id}/calculate-points', [AdminController::class, 'calculatePoints'])->name('calculate-points');
    
    // Utilisateurs
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/users/{id}/edit', [AdminController::class, 'editUser'])->name('edit-user');
    Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('update-user');
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('delete-user');
    
    // Points de vente (Bars)
    Route::get('/bars', [AdminController::class, 'bars'])->name('bars');
    Route::get('/bars/create', [AdminController::class, 'createBar'])->name('create-bar');
    Route::post('/bars', [AdminController::class, 'storeBar'])->name('store-bar');
    Route::get('/bars/{id}/edit', [AdminController::class, 'editBar'])->name('edit-bar');
    Route::put('/bars/{id}', [AdminController::class, 'updateBar'])->name('update-bar');
    Route::post('/bars/{id}/toggle', [AdminController::class, 'toggleBar'])->name('toggle-bar');
    Route::delete('/bars/{id}', [AdminController::class, 'deleteBar'])->name('delete-bar');
    Route::get('/bars/template/download', [AdminController::class, 'downloadBarsTemplate'])->name('download-bars-template');
    Route::post('/bars/import', [AdminController::class, 'importBars'])->name('import-bars');

    // Équipes
    Route::get('/teams', [AdminController::class, 'teams'])->name('teams');
    Route::get('/teams/create', [AdminController::class, 'createTeam'])->name('create-team');
    Route::post('/teams', [AdminController::class, 'storeTeam'])->name('store-team');
    Route::get('/teams/{id}/edit', [AdminController::class, 'editTeam'])->name('edit-team');
    Route::put('/teams/{id}', [AdminController::class, 'updateTeam'])->name('update-team');
    Route::delete('/teams/{id}', [AdminController::class, 'deleteTeam'])->name('delete-team');
    
    // Pronostics
    Route::get('/predictions', [AdminController::class, 'predictions'])->name('predictions');
    Route::delete('/predictions/{id}', [AdminController::class, 'deletePrediction'])->name('delete-prediction');
    
    // Paramètres
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::post('/settings', [AdminController::class, 'updateSettings'])->name('update-settings');

    // Logs OTP
    Route::get('/otp-logs', [AdminController::class, 'otpLogs'])->name('otp-logs');

    // Gestion du tournoi
    Route::get('/tournament', [AdminController::class, 'tournamentManagement'])->name('tournament');
    Route::post('/tournament/generate-bracket', [AdminController::class, 'generateKnockoutBracket'])->name('generate-bracket');
    Route::post('/tournament/calculate-qualified', [AdminController::class, 'calculateQualified'])->name('calculate-qualified');
    Route::get('/tournament/phase/{phase}', [AdminController::class, 'phaseMatches'])->name('phase-matches');
    Route::post('/tournament/qualify-team/{matchId}', [AdminController::class, 'qualifyTeam'])->name('qualify-team');
});
