<?php

use App\Http\Controllers\Admin\PromoCode\PromoCodeController;

Route::prefix('promo-codes')->group(function () {
    Route::get('/', [PromoCodeController::class, 'index']);
    Route::get('/available-durations', [PromoCodeController::class, 'getAvailableDurations']);
    Route::post('/', [PromoCodeController::class, 'store']);
    Route::get('/{id}', [PromoCodeController::class, 'show']);
    Route::put('/{id}', [PromoCodeController::class, 'update']);
    Route::delete('/{id}', [PromoCodeController::class, 'destroy']);
});
