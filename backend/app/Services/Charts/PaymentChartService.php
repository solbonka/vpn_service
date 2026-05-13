<?php

namespace App\Services\Charts;

use App\DTO\Charts\ChartDataRequestDto;
use App\DTO\Charts\ChartDataResponseDto;
use App\Enums\Payment\PaymentStatusEnum;
use App\Models\Payment;
use Carbon\Carbon;

class PaymentChartService
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
                'total_amount' => $dayData['total_amount'],
                'count' => $dayData['count'],
                'average_check' => $dayData['average_check']
            ];
        }

        return $chartData;
    }

    private function getDayData(Carbon $dayStart, Carbon $dayEnd): array
    {
        $dayPayments = Payment::query()
            ->where('status', PaymentStatusEnum::SUCCEEDED)
            ->whereBetween('created_at', [$dayStart, $dayEnd])
            ->get();

        $totalAmount = $dayPayments->sum('amount');
        $count = $dayPayments->count();
        $averageCheck = $count > 0 ? $totalAmount / $count : 0;

        return [
            'total_amount' => $totalAmount,
            'count' => $count,
            'average_check' => round($averageCheck, 2)
        ];
    }
}
