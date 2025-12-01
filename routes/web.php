<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\HomeController;

Route::get('/', [HomeController::class, 'index']);
Route::get('/matches', [HomeController::class, 'matches']);
Route::get('/leaderboard', [HomeController::class, 'leaderboard']);
