<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('bookings:transition-in-progress')->daily();
Schedule::command('bookings:auto-complete')->daily();
Schedule::command('bookings:auto-close')->daily();

Schedule::command('subscriptions:expire')->daily();
Schedule::command('featured-placements:expire')->daily();
Schedule::command('subscriptions:renewal-prompts')->daily();
Schedule::command('providers:aggregate-response-metrics')->daily();
