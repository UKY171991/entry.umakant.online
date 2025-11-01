<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule email transaction processing every 15 minutes
Schedule::command('email:schedule-fetch')
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/email-scheduler.log'));

// Schedule failed transaction retry every hour
Schedule::command('email:process --retry-failed')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/email-retry.log'));

// Schedule cleanup of old processed transactions (weekly)
Schedule::command('email:cleanup --days=90 --keep-failed=30 --force')
    ->weekly()
    ->sundays()
    ->at('02:00')
    ->appendOutputTo(storage_path('logs/email-cleanup.log'));

// Schedule system status check (daily)
Schedule::command('email:status --test-connections')
    ->daily()
    ->at('06:00')
    ->appendOutputTo(storage_path('logs/email-status.log'));

// Schedule health check every 5 minutes
Schedule::command('email-transactions:health-check')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/email-health-check.log'));

// Schedule daily summary report
Schedule::command('email-transactions:send-daily-summary')
    ->daily()
    ->at('08:00')
    ->appendOutputTo(storage_path('logs/email-daily-summary.log'));

// Schedule old data cleanup (weekly)
Schedule::command('email-transactions:cleanup-old-data')
    ->weekly()
    ->sundays()
    ->at('03:00')
    ->appendOutputTo(storage_path('logs/email-data-cleanup.log'));
