<?php

namespace App\Services\Charts;

use App\DTO\Charts\ChartDataRequestDto;
use App\DTO\Charts\ChartDataResponseDto;
use App\Models\ServerMetric;
use Carbon\Carbon;

class UserChartService
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

            if ($dayStart->gt($dateRange['endDate'])) {
                break;
            }

            $dayData = $this->getDayData($dayStart, $dayEnd);

            $chartData[] = [
                'date' => $date->format('Y-m-d'),
                'total_users' => $dayData['total_users'],
                'active_users' => $dayData['active_users'],
                'online_users' => $dayData['online_users']
            ];
        }

        return $chartData;
    }

    private function getDayData(Carbon $dayStart, Carbon $dayEnd): array
    {
        $metrics = ServerMetric::whereBetween('created_at', [$dayStart, $dayEnd])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('server_id')
            ->map(function ($serverMetrics) {
                return $serverMetrics->first();
            });

        $firstServerMetric = $metrics->first();
        $totalUsers = $firstServerMetric ? $firstServerMetric->total_users : 0;
        $activeUsers = $firstServerMetric ? $firstServerMetric->active_users : 0;

        $onlineUsers = $metrics->sum('online_users');

        return [
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'online_users' => $onlineUsers
        ];
    }
}
