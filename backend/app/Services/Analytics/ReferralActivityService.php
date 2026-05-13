<?php

namespace App\Services\Analytics;

use App\Models\ReferralCode;
use App\Models\Subscription;
use Carbon\Carbon;

class ReferralActivityService extends BaseAnalyticsService
{
    /**
     * Получить статистику активности реферальных кодов
     */
    public function getReferralCodeActivity(): array
    {
        $now = Carbon::now();

        $activeCodes = ReferralCode::where('created_at', '>=', $now->subDays(30))
            ->where('is_active', true)
            ->count();

        $recentlyActiveCodes = ReferralCode::whereHas('referredSubscriptions', function ($query) use ($now) {
                $query->where('created_at', '>=', $now->subDays(7));
            })
            ->where('is_active', true)
            ->count();

        $todayActiveCodes = ReferralCode::whereHas('referredSubscriptions', function ($query) use ($now) {
                $query->where('created_at', '>=', $now->subDay());
            })
            ->where('is_active', true)
            ->count();

        $totalActiveCodes = ReferralCode::where('is_active', true)->count();

        $inactiveCodes = ReferralCode::where('is_active', true)
            ->whereDoesntHave('referredSubscriptions', function ($query) use ($now) {
                $query->where('created_at', '>=', $now->subDays(30));
            })
            ->count();

        return [
            'active_codes' => $activeCodes,
            'recently_active_codes' => $recentlyActiveCodes,
            'today_active_codes' => $todayActiveCodes,
            'total_active_codes' => $totalActiveCodes,
            'inactive_codes' => $inactiveCodes,
        ];
    }
}
