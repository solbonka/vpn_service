<?php

use App\Http\Controllers\Admin\Payment\PaymentController;
use App\Http\Controllers\Admin\Server\ServerController;
use App\Http\Controllers\Admin\Subscription\SubscriptionController;

Route::group(['prefix' => 'metrics'], function () {
    Route::get('servers', [ServerController::class, 'getMetrics']);
    Route::get('subscriptions', [SubscriptionController::class, 'getMetrics']);
    Route::get('payments', [PaymentController::class, 'getMetrics']);

    Route::get('charts/users', [ServerController::class, 'getUsersChartData']);
    Route::get('charts/subscriptions', [SubscriptionController::class, 'getSubscriptionsChartData']);
    Route::get('charts/payments', [PaymentController::class, 'getPaymentsChartData']);
});
