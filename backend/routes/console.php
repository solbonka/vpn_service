<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('subscriptions:block-expired')
    ->everyMinute()
    ->withoutOverlapping(5)
    ->runInBackground();

Schedule::command('subscriptions:notify-expiring')
    ->dailyAt('12:00')
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('subscriptions:notify-expired')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();

if (config('telegram.notify_passive_enabled')) {
    Schedule::command('subscriptions:notify-passive')
        ->everyThirtyMinutes()
        ->withoutOverlapping()
        ->runInBackground();

    Schedule::command('subscriptions:notify-inactive-trial')
        ->everyThirtyMinutes()
        ->withoutOverlapping()
        ->runInBackground();

    Schedule::command('subscriptions:notify-inactive-paid')
        ->everyThirtyMinutes()
        ->withoutOverlapping()
        ->runInBackground();
}
