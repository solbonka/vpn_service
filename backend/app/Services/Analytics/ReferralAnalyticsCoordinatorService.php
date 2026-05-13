<?php

namespace App\Services\Analytics;

use App\DTO\Analytics\ReferralStatsDto;
use App\Enums\Lottery\LotteryTicketSourceEnum;
use App\Enums\Payment\PaymentStatusEnum;
use App\Models\LotteryTicket;
use App\Models\Payment;
use App\Models\ReferralCode;
use App\Models\Subscription;

class ReferralAnalyticsCoordinatorService extends BaseAnalyticsService
{
    public function __construct(
        private readonly ReferralStatsService      $statsService,
        private readonly ReferralPeriodService     $periodService,
        private readonly ReferralConversionService $conversionService,
        private readonly ReferralTopService        $topService,
        private readonly ReferralActivityService   $activityService,
        private readonly LotteryAnalyticsService   $lotteryService,
    ) {}

    /**
     * Получить общую статистику реферальной программы
     */
    public function getOverallStats(): array
    {
        try {
            $stats = $this->statsService->getOverallStats();
            return $this->formatResponse($stats->toArray());
        } catch (\Exception $e) {
            return $this->handleException($e, 'Ошибка при получении общей статистики');
        }
    }

    /**
     * Получить статистику по периодам
     */
    public function getStatsByPeriod(string $period = 'month', int $limit = 12): array
    {
        try {
            $stats = $this->periodService->getStatsByPeriod($period, $limit);
            return $this->formatResponse($stats);
        } catch (\Exception $e) {
            return $this->handleException($e, 'Ошибка при получении статистики по периодам');
        }
    }

    /**
     * Получить топ рефереров
     */
    public function getTopReferrers(int $limit = 10): array
    {
        try {
            $topReferrers = $this->topService->getTopReferrers($limit);
            return $this->formatResponse($topReferrers);
        } catch (\Exception $e) {
            return $this->handleException($e, 'Ошибка при получении топа рефереров');
        }
    }

    /**
     * Получить детальную статистику по конкретному реферальному коду
     */
    public function getReferralCodeDetails(string $code): ?array
    {
        try {
            $referralCode = ReferralCode::where('code', $code)
                ->with(['subscription', 'referredSubscriptions'])
                ->first();

            if (!$referralCode) {
                return null;
            }

            $referredSubscriptions = $referralCode->referredSubscriptions;
            $totalReferrals = $referredSubscriptions->count();

            $paymentsStats = Payment::whereIn('subscription_id', $referredSubscriptions->pluck('id'))
                ->where('status', PaymentStatusEnum::SUCCEEDED)
                ->selectRaw('COUNT(*) as payments_count')
                ->selectRaw('SUM(amount) as total_revenue')
                ->selectRaw('AVG(amount) as avg_payment')
                ->first();

            $referralsWithPayments = $referredSubscriptions->filter(function ($subscription) {
                return $subscription->payments()->where('status', PaymentStatusEnum::SUCCEEDED)->exists();
            })->count();

            $conversionRate = $totalReferrals > 0 ? ($referralsWithPayments / $totalReferrals) * 100 : 0;

            $lotteryTickets = LotteryTicket::where('source_id', $referralCode->id)
                ->where('source_type', LotteryTicketSourceEnum::REFERRAL_BONUS)
                ->count();

            return [
                'referral_code' => $referralCode->code,
                'subscription_id' => $referralCode->subscription->id,
                'telegraph_chat_id' => $referralCode->subscription->telegraph_chat_id,
                'is_active' => $referralCode->is_active,
                'created_at' => $referralCode->created_at,
                'total_referrals' => $totalReferrals,
                'payments_count' => $paymentsStats->payments_count ?? 0,
                'total_revenue' => $paymentsStats->total_revenue ?? 0,
                'avg_payment' => $paymentsStats->avg_payment ?? 0,
                'conversion_rate' => round($conversionRate, 2),
                'lottery_tickets' => $lotteryTickets,
            ];
        } catch (\Exception $e) {
            \Log::error('Error getting referral code details', [
                'code' => $code,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Получить статистику лотерейных билетов
     */
    public function getLotteryTicketStats(): array
    {
        try {
            $stats = $this->lotteryService->getLotteryTicketStats();
            return $this->formatResponse($stats->toArray());
        } catch (\Exception $e) {
            return $this->handleException($e, 'Ошибка при получении статистики лотерейных билетов');
        }
    }

    /**
     * Получить статистику конверсии рефералов по периодам
     */
    public function getReferralConversionByPeriod(string $period = 'month', int $limit = 6): array
    {
        try {
            $stats = $this->conversionService->getReferralConversionByPeriod($period, $limit);
            return $this->formatResponse($stats);
        } catch (\Exception $e) {
            return $this->handleException($e, 'Ошибка при получении статистики конверсии по периодам');
        }
    }

    /**
     * Получить статистику конверсии рефералов в платежи по месяцам (для обратной совместимости)
     */
    public function getReferralConversionByMonths(int $months = 6): array
    {
        try {
            $stats = $this->conversionService->getReferralConversionByMonths($months);
            return $this->formatResponse($stats);
        } catch (\Exception $e) {
            return $this->handleException($e, 'Ошибка при получении статистики конверсии по месяцам');
        }
    }

    /**
     * Получить статистику активности реферальных кодов
     */
    public function getReferralCodeActivity(): array
    {
        try {
            $stats = $this->activityService->getReferralCodeActivity();
            return $this->formatResponse($stats);
        } catch (\Exception $e) {
            return $this->handleException($e, 'Ошибка при получении статистики активности кодов');
        }
    }

    /**
     * Получить полный дашборд с основными метриками
     */
    public function getDashboard(string $period = 'month', int $limit = 6): array
    {
        try {
            $dashboard = [
                'overall_stats' => $this->statsService->getOverallStats()->toArray(),
                'period_stats' => $this->periodService->getStatsByPeriod($period, $limit),
                'top_referrers' => $this->topService->getTopReferrers(5),
                'lottery_stats' => $this->lotteryService->getLotteryTicketStats()->toArray(),
                'conversion_stats' => $this->conversionService->getReferralConversionByPeriod($period, $limit),
                'activity_stats' => $this->activityService->getReferralCodeActivity(),
            ];

            return $this->formatResponse($dashboard);
        } catch (\Exception $e) {
            return $this->handleException($e, 'Ошибка при получении данных дашборда');
        }
    }
}
