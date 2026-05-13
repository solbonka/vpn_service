<?php

use App\Http\Controllers\InstructionsController;
use App\Http\Controllers\Subscription\SubscriptionController;
use App\Http\Middleware\EnsureSubscriptionIsActive;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::group([
    'middleware' => [
        EnsureSubscriptionIsActive::class
    ],
], function () {
    Route::get('/open-app/{subscription:token}/{client}', [SubscriptionController::class, 'handleDirectConnection'])
        ->name('direct.connect');
});

Route::group(['prefix' => 'instructions'], function () {
    Route::group(['prefix' => 'setup'], function () {
        Route::get('/{os}', [InstructionsController::class, 'setup'])
            ->where('os', 'android|ios|huawei|mac|windows|android_tv');
    });

    Route::group(['prefix' => 'connection'], function () {
        Route::get('/{os}', [InstructionsController::class, 'connection'])
            ->where('os', 'android|ios|huawei|mac|windows|android_tv');
    });
});
