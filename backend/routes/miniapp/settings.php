<?php
use App\Http\Controllers\Admin\MiniappSettingsController;

Route::prefix('settings')->group(function () {
    Route::get('/', [MiniappSettingsController::class, 'index']);
    Route::put('/logo', [MiniappSettingsController::class, 'updateLogo']);
    Route::delete('/logo', [MiniappSettingsController::class, 'deleteLogo']);
    Route::put('/lottery-image', [MiniappSettingsController::class, 'updateLotteryImage']);
    Route::delete('/lottery-image', [MiniappSettingsController::class, 'deleteLotteryImage']);
});
