<?php

namespace App\DTO\Analytics;

readonly class TopReferrerDto
{
    public function __construct(
        public string  $referralCode,
        public int     $subscriptionId,
        public ?string $telegraphChatId,
        public int     $referralsCount,
        public float   $revenueFromReferrals,
        public string  $createdAt,
    ) {}

    public function toArray(): array
    {
        return [
            'referral_code' => $this->referralCode,
            'subscription_id' => $this->subscriptionId,
            'telegraph_chat_id' => $this->telegraphChatId,
            'referrals_count' => $this->referralsCount,
            'revenue_from_referrals' => $this->revenueFromReferrals,
            'created_at' => $this->createdAt,
        ];
    }
}
