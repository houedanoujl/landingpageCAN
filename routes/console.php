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

// External score sync (football-data.org). Self-skips if disabled or no
// candidate matches in the active window — zero API usage off-tournament.
Schedule::command('matches:sync-scores')
    ->everyTwoMinutes()
    ->withoutOverlapping()
    ->onOneServer()
    ->runInBackground();

// Auto-finalize matches with scores past kickoff +3h, then award points.
// Idempotent: PointLog guard prevents duplicate awards. Acts as the fallback
// when the external API is down or no external_id is set — admin manual entry
// still triggers point processing.
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
