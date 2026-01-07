<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\PredictionController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Web\AdminAuthController;
use App\Http\Controllers\Web\AdminSmsController;
use App\Http\Controllers\Admin\VenueController;

// Pages publiques
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/venues', [HomeController::class, 'venues'])->name('venues');
Route::get('/matches', [HomeController::class, 'matches'])->name('matches');
Route::get('/leaderboard', [HomeController::class, 'leaderboard'])->name('leaderboard');
Route::get('/map', [HomeController::class, 'map'])->name('map');
Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');
Route::get('/animations', [HomeController::class, 'animations'])->name('animations');
Route::get('/calendar', [HomeController::class, 'calendar'])->name('calendar');
Route::get('/conditions', function () {
    return view('terms');
})->name('terms');

// Authentification publique (login classique avec mot de passe)
// Rate limiting pour éviter les abus
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/auth/login', [AuthController::class, 'login'])
    ->middleware('throttle:10,1')
    ->name('auth.login');
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/auth/register', [AuthController::class, 'register'])
    ->middleware('throttle:5,1')
    ->name('auth.register');

// Récupération de mot de passe
Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword'])
    ->middleware('throttle:3,1')
    ->name('auth.reset-password');

// Ancien système OTP (conservé pour compatibilité si nécessaire)
Route::post('/auth/send-otp', [AuthController::class, 'sendOtp'])
    ->middleware('throttle:5,1')
    ->name('auth.send-otp');
Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp'])
    ->middleware('throttle:10,1')
    ->name('auth.verify-otp');
Route::post('/auth/request-new-code', [AuthController::class, 'requestNewCode'])
    ->middleware('throttle:2,60') // Max 2 demandes par heure
    ->name('auth.request-new-code');

Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// Authentification administrateur (par mot de passe)
Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])
    ->middleware('throttle:5,1')
    ->name('admin.auth.login');
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
    Route::post('/matches/{id}/duplicate', [AdminController::class, 'duplicateMatch'])->name('duplicate-match');
    Route::post('/matches/{id}/quick-update', [AdminController::class, 'quickUpdateMatch'])->name('quick-update-match');
    Route::post('/matches/{id}/calculate-points', [AdminController::class, 'calculatePoints'])->name('calculate-points');
    Route::post('/matches/import-json', [AdminController::class, 'importMatchesJson'])->name('import-matches-json');

    // Match-Venue Management (AJAX)
    Route::get('/matches/{matchId}/venues', [AdminController::class, 'getMatchVenues'])->name('match-venues');
    Route::post('/matches/{matchId}/venues/{venueId}/assign', [AdminController::class, 'assignVenueToMatch'])->name('assign-venue-to-match');
    Route::post('/matches/{matchId}/venues/{venueId}/unassign', [AdminController::class, 'unassignVenueFromMatch'])->name('unassign-venue-from-match');

    // Cache Management
    Route::post('/cache/clear', [AdminController::class, 'clearCache'])->name('clear-cache');

    // Calendar & Matrix Views
    Route::get('/calendar', [AdminController::class, 'calendar'])->name('calendar');
    Route::get('/match-venue-matrix', [AdminController::class, 'matchVenueMatrix'])->name('match-venue-matrix');
    Route::get('/match-venue-matrix/export-csv', [AdminController::class, 'exportMatchVenueMatrixCsv'])->name('match-venue-matrix-export-csv');

    // Utilisateurs
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/users/export-csv', [AdminController::class, 'exportUsersCsv'])->name('export-users-csv');
    Route::get('/users/{id}/edit', [AdminController::class, 'editUser'])->name('edit-user');
    Route::get('/users/{id}/export-points-csv', [AdminController::class, 'exportUserPointsCsv'])->name('export-user-points-csv');
    Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('update-user');
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('delete-user');
    Route::post('/users/{id}/reset-points', [AdminController::class, 'resetUserPoints'])->name('reset-user-points');
    
    // Historique des points (logs)
    Route::get('/point-logs', [AdminController::class, 'pointLogs'])->name('point-logs');
    
    // Check-ins (visites des lieux)
    Route::get('/checkins', [AdminController::class, 'checkins'])->name('checkins');
    Route::get('/checkins/export', [AdminController::class, 'exportCheckins'])->name('export-checkins');
    
    // Google Analytics
    Route::get('/analytics', [AdminController::class, 'analytics'])->name('analytics');
    
    // Classement hebdomadaire
    Route::get('/weekly-leaderboard', [AdminController::class, 'weeklyLeaderboard'])->name('weekly-leaderboard');
    Route::get('/weekly-leaderboard/user/{id}', [AdminController::class, 'weeklyLeaderboardUserDetails'])->name('weekly-leaderboard-user-details');
    Route::get('/weekly-leaderboard/export', [AdminController::class, 'exportWeeklyLeaderboard'])->name('export-weekly-leaderboard');
    
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
    Route::get('/bars/{barId}/animations', [AdminController::class, 'barAnimations'])->name('bar-animations');

    // Gestion segmentation PDV (nouvelle interface)
    Route::resource('venues', VenueController::class);
    Route::post('/venues/bulk-update-type', [VenueController::class, 'bulkUpdateType'])->name('venues.bulk-update-type');
    Route::post('/venues/bulk-update-zone', [VenueController::class, 'bulkUpdateZone'])->name('venues.bulk-update-zone');

    // Animations (Venue-Match Links)
    Route::get('/animations', [AdminController::class, 'animations'])->name('animations');
    Route::get('/animations/create', [AdminController::class, 'createAnimation'])->name('create-animation');
    Route::post('/animations', [AdminController::class, 'storeAnimation'])->name('store-animation');
    Route::get('/animations/{id}/edit', [AdminController::class, 'editAnimation'])->name('edit-animation');
    Route::put('/animations/{id}', [AdminController::class, 'updateAnimation'])->name('update-animation');
    Route::post('/animations/{id}/toggle', [AdminController::class, 'toggleAnimation'])->name('toggle-animation');
    Route::delete('/animations/{id}', [AdminController::class, 'deleteAnimation'])->name('delete-animation');

    // Équipes
    Route::get('/teams', [AdminController::class, 'teams'])->name('teams');
    Route::get('/teams/create', [AdminController::class, 'createTeam'])->name('create-team');
    Route::post('/teams', [AdminController::class, 'storeTeam'])->name('store-team');
    Route::get('/teams/{id}/edit', [AdminController::class, 'editTeam'])->name('edit-team');
    Route::put('/teams/{id}', [AdminController::class, 'updateTeam'])->name('update-team');
    Route::delete('/teams/{id}', [AdminController::class, 'deleteTeam'])->name('delete-team');

    // Stades
    Route::get('/stadiums', [AdminController::class, 'stadiums'])->name('stadiums');
    Route::get('/stadiums/create', [AdminController::class, 'createStadium'])->name('create-stadium');
    Route::post('/stadiums', [AdminController::class, 'storeStadium'])->name('store-stadium');
    Route::get('/stadiums/{id}/edit', [AdminController::class, 'editStadium'])->name('edit-stadium');
    Route::put('/stadiums/{id}', [AdminController::class, 'updateStadium'])->name('update-stadium');
    Route::delete('/stadiums/{id}', [AdminController::class, 'deleteStadium'])->name('delete-stadium');

    // Pronostics
    Route::get('/predictions', [AdminController::class, 'predictions'])->name('predictions');
    Route::get('/matches/{id}/predictions', [AdminController::class, 'matchPredictions'])->name('match-predictions');
    Route::delete('/predictions/{id}', [AdminController::class, 'deletePrediction'])->name('delete-prediction');
    Route::post('/predictions/bulk-delete', [AdminController::class, 'bulkDeletePredictions'])->name('bulk-delete-predictions');
    Route::get('/predictions/bulk-delete', function() {
        return redirect()->route('admin.predictions')->with('error', 'Action invalide. Utilisez le formulaire pour supprimer des pronostics.');
    });
    Route::post('/matches/bulk-delete', [AdminController::class, 'bulkDeleteMatches'])->name('bulk-delete-matches');
    Route::get('/matches/bulk-delete', function() {
        return redirect()->route('admin.matches')->with('error', 'Action invalide. Utilisez le formulaire pour supprimer des matchs.');
    });
    
    // Paramètres
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::post('/settings', [AdminController::class, 'updateSettings'])->name('update-settings');

    // Logs OTP
    Route::get('/otp-logs', [AdminController::class, 'otpLogs'])->name('otp-logs');

    // Médias Animations (Highlights & Vidéos)
    Route::get('/media', [AdminController::class, 'media'])->name('media');
    Route::get('/media/create', [AdminController::class, 'createMedia'])->name('create-media');
    Route::post('/media', [AdminController::class, 'storeMedia'])->name('store-media');
    Route::get('/media/{id}/edit', [AdminController::class, 'editMedia'])->name('edit-media');
    Route::put('/media/{id}', [AdminController::class, 'updateMedia'])->name('update-media');
    Route::delete('/media/{id}', [AdminController::class, 'deleteMedia'])->name('delete-media');
    Route::post('/media/{id}/toggle', [AdminController::class, 'toggleMedia'])->name('toggle-media');

    // Gestion du tournoi
    Route::get('/tournament', [AdminController::class, 'tournamentManagement'])->name('tournament');
    Route::post('/tournament/generate-bracket', [AdminController::class, 'generateKnockoutBracket'])->name('generate-bracket');
    Route::post('/tournament/calculate-qualified', [AdminController::class, 'calculateQualified'])->name('calculate-qualified');
    Route::get('/tournament/phase/{phase}', [AdminController::class, 'phaseMatches'])->name('phase-matches');
    Route::post('/tournament/qualify-team/{matchId}', [AdminController::class, 'qualifyTeam'])->name('qualify-team');

    // Envoi de SMS
    Route::get('/sms', [AdminSmsController::class, 'index'])->name('sms');
    Route::post('/sms/send', [AdminSmsController::class, 'send'])->name('sms.send');
    Route::post('/sms/test', [AdminSmsController::class, 'sendTest'])->name('sms.test');
});
