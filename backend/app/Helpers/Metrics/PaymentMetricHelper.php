<?php

namespace App\Helpers\Metrics;

use App\Enums\Payment\PaymentStatusEnum;
use App\Http\Resources\Metrics\PaymentMetricResource;
use App\Models\Payment;
use Carbon\Carbon;

class PaymentMetricHelper
{
    public static function aggregate(): PaymentMetricResource
    {
        $monthStart = Carbon::now()->startOfMonth();

        $totalIncome = Payment::query()
            ->where('status', PaymentStatusEnum::SUCCEEDED)
            ->sum('amount');

        $baseTotalIncome = Payment::query()
            ->where('status', PaymentStatusEnum::SUCCEEDED)
            ->whereDate('created_at', '<', $monthStart)
            ->sum('amount');

        $monthlyIncome = Payment::query()
            ->where('status', PaymentStatusEnum::SUCCEEDED)
            ->whereDate('created_at', '>=', $monthStart)
            ->sum('amount');

        $prevMonthlyIncome = Payment::query()
            ->where('status', PaymentStatusEnum::SUCCEEDED)
            ->whereBetween('created_at', [
                $monthStart->copy()->subMonth(),
                $monthStart->copy()->subDay()
            ])
            ->sum('amount');

        $totalCount = Payment::query()
            ->where('status', PaymentStatusEnum::SUCCEEDED)
            ->count();

        $averageCheck = $totalCount > 0 ? $totalIncome / $totalCount : 0;

        $monthlyCount = Payment::query()
            ->where('status', PaymentStatusEnum::SUCCEEDED)
            ->whereDate('created_at', '>=', $monthStart)
            ->count();

        $monthlyAverageCheck = $monthlyCount > 0 ? $monthlyIncome / $monthlyCount : 0;

        $prevTotalCount = Payment::query()
            ->where('status', PaymentStatusEnum::SUCCEEDED)
            ->whereDate('created_at', '<', $monthStart)
            ->count();

        $prevAverageCheck = $prevTotalCount > 0 ? $baseTotalIncome / $prevTotalCount : 0;

        $prevMonthlyCount = Payment::query()
            ->where('status', PaymentStatusEnum::SUCCEEDED)
            ->whereBetween('created_at', [
                $monthStart->copy()->subMonth(),
                $monthStart->copy()->subDay()
            ])
            ->count();

        $prevMonthlyAverageCheck = $prevMonthlyCount > 0 ? $prevMonthlyIncome / $prevMonthlyCount : 0;


        $calculateGrowth = function ($current, $base) {
            if ($base == 0) return $current > 0 ? 100 : 0;
            return round((($current - $base) / $base) * 100, 1);
        };

        return new PaymentMetricResource([
            'total_income'                 => $totalIncome,
            'total_income_growth'          => $calculateGrowth($totalIncome, $baseTotalIncome),
            'monthly_income'               => $monthlyIncome,
            'monthly_income_growth'        => $calculateGrowth($monthlyIncome, $prevMonthlyIncome),
            'average_check'                => round($averageCheck, 2),
            'average_check_growth'         => $calculateGrowth($averageCheck, $prevAverageCheck),
            'monthly_average_check'        => round($monthlyAverageCheck, 2),
            'monthly_average_check_growth' => $calculateGrowth($monthlyAverageCheck, $prevMonthlyAverageCheck),
        ]);
    }
}
