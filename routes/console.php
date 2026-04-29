<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Daily attendance seeding at 07:00 each school day
Schedule::command('attendance:seed-today')
    ->weekdays()->dailyAt('07:00')
    ->timezone('Asia/Kuala_Lumpur');

// Queue retry of failed jobs every 10 min (for FR image flush etc.)
Schedule::command('queue:work --stop-when-empty')
    ->everyTenMinutes()->withoutOverlapping();
