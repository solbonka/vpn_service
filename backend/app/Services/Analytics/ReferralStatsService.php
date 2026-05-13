<?php

namespace App\Services\Analytics;

use App\DTO\Analytics\ReferralStatsDto;
use App\Enums\Payment\PaymentStatusEnum;
use App\Models\BonusAccount;
use App\Models\LotteryTicket;
use App\Models\Payment;
use App\Models\ReferralCode;
use App\Models\Subscription;

class ReferralStatsService extends BaseAnalyticsService
{
    /**
     * Получить общую статистику реферальной программы
     */
    public function getOverallStats(): ReferralStatsDto
    {
        $totalReferralCodes = ReferralCode::where('is_active', true)->count();
        $totalReferrals = Subscription::whereNotNull('referred_by_code_id')->count();
        $totalLotteryTickets = LotteryTicket::count();
        $totalBonusAccounts = BonusAccount::count();

        $referralsWithPayments = Subscription::whereNotNull('referred_by_code_id')
            ->whereHas('payments', function ($query) {
                $query->where('status', PaymentStatusEnum::SUCCEEDED);
            })
            ->count();

        $conversionRate = $totalReferrals > 0 ? ($referralsWithPayments / $totalReferrals) * 100 : 0;

        $totalReferralRevenue = Payment::whereHas('subscription', function ($query) {
                $query->whereNotNull('referred_by_code_id');
            })
            ->where('status', PaymentStatusEnum::SUCCEEDED)
            ->sum('amount');

        return new ReferralStatsDto(
            totalReferralCodes: $totalReferralCodes,
            totalReferrals: $totalReferrals,
            totalLotteryTickets: $totalLotteryTickets,
            totalBonusAccounts: $totalBonusAccounts,
            referralsWithPayments: $referralsWithPayments,
            conversionRate: round($conversionRate, 2),
            totalReferralRevenue: $totalReferralRevenue,
        );
    }
}
