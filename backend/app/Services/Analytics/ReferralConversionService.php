<?php

namespace App\Services\Analytics;

use App\DTO\Analytics\ConversionStatsDto;
use App\Enums\Payment\PaymentStatusEnum;
use App\Models\Payment;
use App\Models\Subscription;
use Carbon\Carbon;

class ReferralConversionService extends BaseAnalyticsService
{
    /**
     * Получить статистику конверсии рефералов в платежи по периодам
     */
    public function getReferralConversionByPeriod(string $period = 'month', int $limit = 6): array
    {
        $results = [];
        $dateFormat = $this->getDateFormat($period);

        for ($i = 0; $i < $limit; $i++) {
            $startDate = match($period) {
                'day' => Carbon::now()->subDays($i + 1)->startOfDay(),
                'week' => Carbon::now()->subWeeks($i + 1)->startOfWeek(),
                'month' => Carbon::now()->subMonths($i + 1)->startOfMonth(),
                'year' => Carbon::now()->subYears($i + 1)->startOfYear(),
                default => Carbon::now()->subMonths($i + 1)->startOfMonth()
            };

            $endDate = match($period) {
                'day' => Carbon::now()->subDays($i + 1)->endOfDay(),
                'week' => Carbon::now()->subWeeks($i + 1)->endOfWeek(),
                'month' => Carbon::now()->subMonths($i + 1)->endOfMonth(),
                'year' => Carbon::now()->subYears($i + 1)->endOfYear(),
                default => Carbon::now()->subMonths($i + 1)->endOfMonth()
            };

        $conversionStats = $this->calculateConversionForPeriod($startDate, $endDate, $period);
        $results[] = $conversionStats->toArray();
        }

        return array_reverse($results);
    }

    /**
     * Получить статистику конверсии рефералов в платежи по месяцам (для обратной совместимости)
     */
    public function getReferralConversionByMonths(int $months = 6): array
    {
        return $this->getReferralConversionByPeriod('month', $months);
    }

    /**
     * Рассчитать конверсию для конкретного периода
     */
    private function calculateConversionForPeriod(Carbon $startDate, Carbon $endDate, string $period): ConversionStatsDto
    {
        $newReferrals = Subscription::whereNotNull('referred_by_code_id')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $totalNewReferrals = $newReferrals->count();

        if ($totalNewReferrals === 0) {
            return new ConversionStatsDto(
                period: $this->formatPeriod($startDate, $period),
                newReferrals: 0,
                convertedReferrals: 0,
                conversionRate: 0,
                revenue: 0,
            );
        }

        $convertedReferrals = $newReferrals->filter(function ($subscription) {
            return $subscription->payments()->where('status', PaymentStatusEnum::SUCCEEDED)->exists();
        })->count();

        $revenue = Payment::whereIn('subscription_id', $newReferrals->pluck('id'))
            ->where('status', PaymentStatusEnum::SUCCEEDED)
            ->sum('amount');

        $conversionRate = ($convertedReferrals / $totalNewReferrals) * 100;

        return new ConversionStatsDto(
            period: $this->formatPeriod($startDate, $period),
            newReferrals: $totalNewReferrals,
            convertedReferrals: $convertedReferrals,
            conversionRate: round($conversionRate, 2),
            revenue: $revenue,
        );
    }
}
