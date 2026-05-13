<?php

namespace App\Services\Charts;

use App\DTO\Charts\ChartDataRequestDto;
use App\DTO\Charts\ChartDataResponseDto;
use App\Enums\Subscription\SubscriptionStatusEnum;
use App\Models\Subscription;
use Carbon\Carbon;

class SubscriptionChartService
{
    public function getChartData(ChartDataRequestDto $request): ChartDataResponseDto
    {
        $dateRange = $this->calculateDateRange($request);
        $chartData = $this->buildChartData($dateRange);

        return new ChartDataResponseDto(
            data: $chartData,
            period: $request->period
        );
    }

    private function calculateDateRange(ChartDataRequestDto $request): array
    {
        if ($request->isCustomDateRange()) {
            $startDate = Carbon::parse($request->startDate)->startOfDay();
            $endDate = Carbon::parse($request->endDate)->endOfDay();
            $days = $request->getDaysCount();
        } else {
            $days = $request->getDaysCount();
            $startDate = Carbon::now()->subDays($days - 1)->startOfDay();
            $endDate = Carbon::now()->endOfDay();
        }

        return [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'days' => $days
        ];
    }

    private function buildChartData(array $dateRange): array
    {
        $chartData = [];

        for ($i = 0; $i < $dateRange['days']; $i++) {
            $date = $dateRange['startDate']->copy()->addDays($i);
            $dayStart = $date->copy()->startOfDay();
            $dayEnd = $date->copy()->endOfDay();

            // Проверяем, что день не превышает конечную дату
            if ($dayStart->gt($dateRange['endDate'])) {
                break;
            }

            $dayData = $this->getDayData($dayStart, $dayEnd);

            $chartData[] = [
                'date' => $date->format('Y-m-d'),
                'active' => $dayData['active'],
                'blocked' => $dayData['blocked'],
                'new' => $dayData['new']
            ];
        }

        return $chartData;
    }

    private function getDayData(Carbon $dayStart, Carbon $dayEnd): array
    {
        $activeSubscriptions = Subscription::query()
            ->where('status', SubscriptionStatusEnum::ACTIVE)
            ->whereHas('plan', function ($query) {
                $query->where('name', '!=', 'Пробный');
            })
            ->where('updated_at', '<=', $dayEnd)
            ->count();

        $blockedSubscriptions = Subscription::query()
            ->where('status', SubscriptionStatusEnum::BLOCKED)
            ->whereHas('plan', function ($query) {
                $query->where('name', '!=', 'Пробный');
            })
            ->where('updated_at', '<=', $dayEnd)
            ->count();

        $newSubscriptions = Subscription::query()
            ->where('status', SubscriptionStatusEnum::ACTIVE)
            ->whereHas('plan', function ($query) {
                $query->where('name', '!=', 'Пробный');
            })
            ->whereBetween('updated_at', [$dayStart, $dayEnd])
            ->count();

        return [
            'active' => $activeSubscriptions,
            'blocked' => $blockedSubscriptions,
            'new' => $newSubscriptions
        ];
    }
}
