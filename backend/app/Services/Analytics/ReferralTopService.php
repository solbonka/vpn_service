<?php

namespace App\Services\Analytics;

use App\DTO\Analytics\TopReferrerDto;
use App\Enums\Payment\PaymentStatusEnum;
use App\Models\Payment;
use App\Models\ReferralCode;

class ReferralTopService extends BaseAnalyticsService
{
    /**
     * Получить топ рефереров
     */
    public function getTopReferrers(int $limit = 10): array
    {
        $referralCodes = ReferralCode::with('subscription')
            ->whereHas('referredSubscriptions')
            ->withCount('referredSubscriptions')
            ->orderBy('referred_subscriptions_count', 'desc')
            ->limit($limit)
            ->get();

        return $referralCodes->map(function ($referralCode) {
            $subscription = $referralCode->subscription;

            $revenue = Payment::whereHas('subscription', function ($query) use ($referralCode) {
                    $query->where('referred_by_code_id', $referralCode->id);
                })
                ->where('status', PaymentStatusEnum::SUCCEEDED)
                ->sum('amount');

            return new TopReferrerDto(
                referralCode: $referralCode->code,
                subscriptionId: $subscription->id,
                telegraphChatId: $subscription->telegraph_chat_id,
                referralsCount: $referralCode->referred_subscriptions_count,
                revenueFromReferrals: $revenue,
                createdAt: $referralCode->created_at->toISOString(),
            );
        })->map(fn($dto) => $dto->toArray())->toArray();
    }
}
