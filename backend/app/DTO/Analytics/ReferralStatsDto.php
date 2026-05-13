<?php

namespace App\DTO\Analytics;

readonly class ReferralStatsDto
{
    public function __construct(
        public int   $totalReferralCodes,
        public int   $totalReferrals,
        public int   $totalLotteryTickets,
        public int   $totalBonusAccounts,
        public int   $referralsWithPayments,
        public float $conversionRate,
        public float $totalReferralRevenue,
    ) {}

    public function toArray(): array
    {
        return [
            'total_referral_codes' => $this->totalReferralCodes,
            'total_referrals' => $this->totalReferrals,
            'total_lottery_tickets' => $this->totalLotteryTickets,
            'total_bonus_accounts' => $this->totalBonusAccounts,
            'referrals_with_payments' => $this->referralsWithPayments,
            'conversion_rate' => $this->conversionRate,
            'total_referral_revenue' => $this->totalReferralRevenue,
        ];
    }
}
