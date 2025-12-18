<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Scheduled commands for WhatsApp notifications
Schedule::command('notifications:send-match-reminders')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->onOneServer()
    ->runInBackground();

Schedule::command('notifications:send-match-results')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->onOneServer()
    ->runInBackground();

// Process finished matches and calculate points automatically
Schedule::command('matches:process-finished')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->onOneServer()
    ->runInBackground();

// Clean old log files daily at 2 AM
Schedule::command('logs:clean --days=7')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->onOneServer();
