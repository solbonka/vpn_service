<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Queue\Events\JobFailed;
use App\Listeners\SendJobFailedNotification;

class EventServiceProvider extends ServiceProvider
{

    protected $listen = [
        JobFailed::class => [
            SendJobFailedNotification::class,
        ],
    ];

    public function boot(): void
    {
        parent::boot();
    }
}
