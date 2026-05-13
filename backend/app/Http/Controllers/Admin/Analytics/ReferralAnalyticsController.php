<?php

namespace App\Http\Controllers\Admin\Analytics;

use App\Http\Controllers\Controller;
use App\Services\Analytics\ReferralAnalyticsCoordinatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReferralAnalyticsController extends Controller
{
    public function __construct(
        private readonly ReferralAnalyticsCoordinatorService $referralAnalyticsService
    ) {}

    /**
     * Получить общую статистику реферальной программы
     */
    public function getOverallStats(): JsonResponse
    {
        $result = $this->referralAnalyticsService->getOverallStats();
        return response()->json($result);
    }

    /**
     * Получить статистику по периодам
     */
    public function getStatsByPeriod(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month');
        $limit = (int) $request->get('limit', 12);

        $result = $this->referralAnalyticsService->getStatsByPeriod($period, $limit);
        return response()->json($result);
    }

    /**
     * Получить топ рефереров
     */
    public function getTopReferrers(Request $request): JsonResponse
    {
        $limit = (int) $request->get('limit', 10);
        $result = $this->referralAnalyticsService->getTopReferrers($limit);
        return response()->json($result);
    }

    /**
     * Получить детальную информацию по реферальному коду
     */
    public function getReferralCodeDetails(Request $request, string $code): JsonResponse
    {
        $details = $this->referralAnalyticsService->getReferralCodeDetails($code);

        if (!$details) {
            return response()->json([
                'success' => false,
                'message' => 'Реферальный код не найден'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $details
        ]);
    }

    /**
     * Получить статистику лотерейных билетов
     */
    public function getLotteryTicketStats(): JsonResponse
    {
        $result = $this->referralAnalyticsService->getLotteryTicketStats();
        return response()->json($result);
    }

    /**
     * Получить статистику конверсии рефералов по периодам
     */
    public function getReferralConversionByPeriod(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month');
        $limit = (int) $request->get('limit', 6);
        $result = $this->referralAnalyticsService->getReferralConversionByPeriod($period, $limit);
        return response()->json($result);
    }

    /**
     * Получить статистику конверсии рефералов по месяцам (для обратной совместимости)
     */
    public function getReferralConversionByMonths(Request $request): JsonResponse
    {
        $months = (int) $request->get('months', 6);
        $result = $this->referralAnalyticsService->getReferralConversionByMonths($months);
        return response()->json($result);
    }

    /**
     * Получить статистику активности реферальных кодов
     */
    public function getReferralCodeActivity(): JsonResponse
    {
        $result = $this->referralAnalyticsService->getReferralCodeActivity();
        return response()->json($result);
    }

    /**
     * Получить полный дашборд с основными метриками
     */
    public function getDashboard(Request $request): JsonResponse
    {
        try {
            $period = $request->get('period', 'month');
            $limit = (int) $request->get('limit', 6);

            $result = $this->referralAnalyticsService->getDashboard($period, $limit);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении данных дашборда',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
