<?php

namespace App\Helpers\Metrics;

use App\Http\Resources\Metrics\SubscriptionMetricResource;
use App\Models\Subscription;
use App\Enums\Subscription\SubscriptionStatusEnum;
use Carbon\Carbon;

class SubscriptionMetricHelper
{
    public static function aggregate(): SubscriptionMetricResource
    {
        $monthStart = Carbon::now()->startOfMonth();

        $totalActive = Subscription::query()
            ->where('status', SubscriptionStatusEnum::ACTIVE)
            ->whereHas('plan', function ($query) {
                $query->where('name', '!=', 'Пробный');
            })
            ->count();

        $totalBlocked = Subscription::query()
            ->where('status', SubscriptionStatusEnum::BLOCKED)
            ->whereHas('plan', function ($query) {
                $query->where('name', '!=', 'Пробный');
            })
            ->count();

        $totalNew = Subscription::query()
            ->where('status', SubscriptionStatusEnum::ACTIVE)
            ->whereHas('plan', function ($query) {
                $query->where('name', '!=', 'Пробный');
            })
            ->whereDate('updated_at', '>=', $monthStart)
            ->count();

        $baseActive = Subscription::query()
            ->where('status', SubscriptionStatusEnum::ACTIVE)
            ->whereHas('plan', function ($query) {
                $query->where('name', '!=', 'Пробный');
            })
            ->whereDate('updated_at', '<', $monthStart)
            ->count();

        $baseBlocked = Subscription::query()
            ->where('status', SubscriptionStatusEnum::BLOCKED)
            ->whereHas('plan', function ($query) {
                $query->where('name', '!=', 'Пробный');
            })
            ->whereDate('updated_at', '<', $monthStart)
            ->count();

        $baseNew = Subscription::query()
            ->where('status', SubscriptionStatusEnum::ACTIVE)
            ->whereHas('plan', function ($query) {
                $query->where('name', '!=', 'Пробный');
            })
            ->whereDate('updated_at', '<', $monthStart)
            ->count();

        $calculateGrowth = function ($current, $base) {
            if ($base == 0) return $current > 0 ? 100 : 0;
            return round((($current - $base) / $base) * 100, 1);
        };

        return new SubscriptionMetricResource([
            'sub_active_total' => $totalActive,
            'sub_active_growth' => $calculateGrowth($totalActive, $baseActive),
            'sub_blocked_total' => $totalBlocked,
            'sub_blocked_growth' => $calculateGrowth($totalBlocked, $baseBlocked),
            'sub_new_total' => $totalNew,
            'sub_new_growth' => $calculateGrowth($totalNew, $baseNew)
        ]);
    }
}
