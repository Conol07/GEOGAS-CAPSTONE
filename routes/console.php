<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Daily reminder to managers whose prices are overdue. Needs the Laravel
// scheduler running (cron: * * * * * php artisan schedule:run) and a real MAIL_* config.
Illuminate\Support\Facades\Schedule::command('geogas:remind-stale')->dailyAt('08:00');

// Pulls Philippine fuel headlines from the feeds in GEOGAS_NEWS_FEEDS into the LGU's drafts (no-op if none are set).
Illuminate\Support\Facades\Schedule::command('geogas:import-national-news')->dailyAt('07:00');
