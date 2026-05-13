<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use R3bzya\Helpers\Support\RoutePattern;

class RouteServiceProvider extends ServiceProvider
{
    const INT_ROUTES = [

    ];

    const UUID_ROUTES = [

    ];

    public function boot(): void
    {
        $this->bootRoutePatterns();
    }

    private function bootRoutePatterns(): void
    {
        foreach (self::INT_ROUTES as $route) {
            Route::pattern($route, RoutePattern::INT);
        }

        foreach (self::UUID_ROUTES as $route) {
            Route::pattern($route, RoutePattern::UUID);
        }
    }
}
