<?php

namespace App\Providers;

use App\Console\Commands\ErrorMonitoringStatusCommand;
use App\Console\Commands\SetupErrorMonitoringCommand;
use App\Exceptions\GlobalErrorHandler;
use App\Services\Telegram\ErrorNotificationService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Support\ServiceProvider;

class ErrorMonitoringServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(ErrorNotificationService::class, function ($app) {
            return new ErrorNotificationService();
        });

        $this->app->singleton(Handler::class, function (Application $app) {
            return new GlobalErrorHandler($app);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {

        if ($this->app->runningInConsole()) {
            $this->commands([
                SetupErrorMonitoringCommand::class,
                ErrorMonitoringStatusCommand::class,
            ]);
        }
    }
}
