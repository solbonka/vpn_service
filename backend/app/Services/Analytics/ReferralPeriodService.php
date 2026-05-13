<?php

namespace App\Services\Analytics;

use App\DTO\Analytics\PeriodStatsDto;
use App\Enums\Lottery\LotteryTicketSourceEnum;
use App\Enums\Payment\PaymentStatusEnum;
use App\Models\LotteryTicket;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Support\Collection;

class ReferralPeriodService extends BaseAnalyticsService
{
    /**
     * Получить статистику по периодам
     */
    public function getStatsByPeriod(string $period = 'month', int $limit = 12): array
    {
        $dateFormat = $this->getDateFormat($period);

        $referralStats = $this->getReferralStatsByPeriod($period, $limit, $dateFormat);

        $paymentStats = $this->getPaymentStatsByPeriod($period, $limit, $dateFormat);

        $lotteryStats = $this->getLotteryStatsByPeriod($period, $limit, $dateFormat);

        return [
            'referral_stats' => $referralStats,
            'payment_stats' => $paymentStats,
            'lottery_stats' => $lotteryStats,
        ];
    }

    /**
     * Получить статистику рефералов по периодам
     */
    private function getReferralStatsByPeriod(string $period, int $limit, string $dateFormat): Collection
    {
        return Subscription::whereNotNull('referred_by_code_id')
            ->selectRaw("TO_CHAR(created_at, '{$dateFormat}') as period")
            ->selectRaw('COUNT(*) as new_referrals')
            ->groupBy('period')
            ->orderBy('period', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Получить статистику платежей по периодам
     */
    private function getPaymentStatsByPeriod(string $period, int $limit, string $dateFormat): Collection
    {
        return Payment::whereHas('subscription', function ($query) {
                $query->whereNotNull('referred_by_code_id');
            })
            ->where('status', PaymentStatusEnum::SUCCEEDED)
            ->selectRaw("TO_CHAR(created_at, '{$dateFormat}') as period")
            ->selectRaw('COUNT(*) as payments_count')
            ->selectRaw('SUM(amount) as revenue')
            ->groupBy('period')
            ->orderBy('period', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Получить статистику лотерейных билетов по периодам
     */
    private function getLotteryStatsByPeriod(string $period, int $limit, string $dateFormat): Collection
    {
        return LotteryTicket::selectRaw("TO_CHAR(created_at, '{$dateFormat}') as period")
            ->selectRaw('COUNT(*) as tickets_count')
            ->selectRaw('source_type')
            ->groupBy('period', 'source_type')
            ->orderBy('period', 'desc')
            ->limit($limit * 2)
            ->get()
            ->groupBy('period');
    }
}
