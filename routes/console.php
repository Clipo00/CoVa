<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule disposable email domain list updates (weekly via package releases)
use Illuminate\Support\Facades\Schedule;

Schedule::command('disposable:update')->weekly();

// Database backup — daily at 3 AM UTC
Schedule::command('backup:database')->dailyAt('03:00');
