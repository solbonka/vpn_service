<?php

use App\Http\Controllers\Admin\Server\ServerController;

Route::get('marzban/servers', [ServerController::class, 'getMarzbanServers']);
Route::get('marzban/servers/metrics', [ServerController::class, 'getMarzbanMetrics']);

Route::get('remnawave/hosts', [ServerController::class, 'getRemnawaveHosts']);
Route::get('remnawave/hosts/metrics', [ServerController::class, 'getRemnawaveHostsMetrics']);

Route::get('servers', [ServerController::class, 'getMarzbanServers']);
