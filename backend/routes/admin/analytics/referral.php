<?php

use App\Http\Controllers\Admin\Analytics\ReferralAnalyticsController;

Route::prefix('referral-analytics')->group(function () {
    // Общая статистика
    Route::get('overview', [ReferralAnalyticsController::class, 'getOverallStats']);
    
    // Полный дашборд
    Route::get('dashboard', [ReferralAnalyticsController::class, 'getDashboard']);
    
    // Статистика по периодам
    Route::get('stats-by-period', [ReferralAnalyticsController::class, 'getStatsByPeriod']);
    
    // Топ рефереров
    Route::get('top-referrers', [ReferralAnalyticsController::class, 'getTopReferrers']);
    
    // Детали реферального кода
    Route::get('referral-code/{code}', [ReferralAnalyticsController::class, 'getReferralCodeDetails']);
    
    // Статистика лотерейных билетов
    Route::get('lottery-stats', [ReferralAnalyticsController::class, 'getLotteryTicketStats']);
    
    // Конверсия рефералов по периодам
    Route::get('conversion-by-period', [ReferralAnalyticsController::class, 'getReferralConversionByPeriod']);
    
    // Конверсия рефералов по месяцам (для обратной совместимости)
    Route::get('conversion-by-months', [ReferralAnalyticsController::class, 'getReferralConversionByMonths']);
    
    // Активность реферальных кодов
    Route::get('code-activity', [ReferralAnalyticsController::class, 'getReferralCodeActivity']);
});
