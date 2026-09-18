<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('orders:sync-courier-status')->everyThirtyMinutes();
Schedule::command('sitemap:generate')->daily();
Schedule::command('db:backup-run')->dailyAt('02:00');
