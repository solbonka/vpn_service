<?php

namespace App\Services\Analytics;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

abstract class BaseAnalyticsService
{
    /**
     * Форматировать ответ с данными
     */
    protected function formatResponse(array $data): array
    {
        return [
            'success' => true,
            'data' => $data
        ];
    }

    /**
     * Обработать исключение и вернуть ошибку
     */
    protected function handleException(\Exception $e, string $message): array
    {
        Log::error('Analytics service error', [
            'service' => static::class,
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return [
            'success' => false,
            'message' => $message,
            'error' => $e->getMessage()
        ];
    }

    /**
     * Получить диапазон дат для периода
     */
    protected function getDateRange(string $period, int $limit): array
    {
        $ranges = [];
        
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

            $ranges[] = [
                'start' => $startDate,
                'end' => $endDate,
                'period' => $this->formatPeriod($startDate, $period)
            ];
        }

        return array_reverse($ranges);
    }

    /**
     * Форматировать период в зависимости от типа
     */
    protected function formatPeriod(Carbon $date, string $period): string
    {
        return match($period) {
            'day' => $date->format('Y-m-d'),
            'week' => $date->format('Y-\WW'),
            'month' => $date->format('Y-m'),
            'year' => $date->format('Y'),
            default => $date->format('Y-m')
        };
    }

    /**
     * Получить формат даты для SQL запросов
     */
    protected function getDateFormat(string $period): string
    {
        return match($period) {
            'day' => 'YYYY-MM-DD',
            'week' => 'YYYY-"W"WW',
            'month' => 'YYYY-MM',
            'year' => 'YYYY',
            default => 'YYYY-MM'
        };
    }
}
