<?php

namespace App\Services\Subscription;

use App\Actions\Subscription\StoreSubscriptionAction;
use App\Actions\Subscription\UpdateSubscriptionAction;
use App\DTO\Actions\Subscription\StoreSubscriptionActionDto;
use App\DTO\Actions\Subscription\UpdateSubscriptionActionDto;
use App\Enums\Subscription\SubscriptionStatusEnum;
use App\Helpers\SubscriptionHelper;
use App\Models\BonusAccount;
use App\Models\Duration;
use App\Models\LotteryTicket;
use App\Models\Plan;
use App\Models\ReferralCode;
use App\Models\Subscription;
use App\Services\Lottery\LotteryTicketService;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SubscriptionService
{
    private TelegraphChat $chat;
    private LotteryTicketService $lotteryTicketService;

    public function __construct(TelegraphChat $chat, LotteryTicketService $lotteryTicketService)
    {
        $this->chat = $chat;
        $this->lotteryTicketService = $lotteryTicketService;
    }

    public function store(): ?Subscription
    {
        $plan = Plan::query()->where('price', 0)->first();
        $duration = Duration::query()->where('is_trial', true)->first();

        $subscription = app(StoreSubscriptionAction::class)->execute(new StoreSubscriptionActionDto(
            token: str::random(32),
            telegraphChatId: $this->chat->id,
            planId: $plan->id,
            durationId: $duration->id,
            endDatetime: now()->addDays($duration->days)
        ));

        if ($subscription) {
            // Создаем только реферальный код и бонусный счет, но НЕ лотерейные билеты
            // Билеты начисляются только за платные подписки
            $this->createReferralCodeAndBonusAccount($subscription);
        }

        return $subscription;
    }

    public function update(
        Subscription $subscription,
        ?int         $planId = null,
        ?int         $durationId = null,
        bool         $enabled = true,
    ): ?Subscription
    {
        Log::info('SubscriptionService::update started', [
            'subscription_id' => $subscription->id,
            'current_status' => $subscription->status->value,
            'current_plan_id' => $subscription->plan_id,
            'current_duration_id' => $subscription->duration_id,
            'current_end_datetime' => $subscription->end_datetime,
            'input_plan_id' => $planId,
            'input_duration_id' => $durationId,
            'enabled' => $enabled
        ]);

        $plan = $subscription->plan;
        $duration = $subscription->duration;

        Log::info('Default plan and duration lookup', [
            'subscription_id' => $subscription->id,
            'default_plan_id' => $plan?->id,
            'default_plan_name' => $plan?->name,
            'default_duration_id' => $duration?->id,
            'default_duration_days' => $duration?->days
        ]);

        if ($planId && $durationId) {
            $plan = Plan::query()->find($planId);
            $duration = Duration::query()->find($durationId);

            Log::info('Custom plan and duration lookup', [
                'subscription_id' => $subscription->id,
                'requested_plan_id' => $planId,
                'requested_duration_id' => $durationId,
                'found_plan_id' => $plan?->id,
                'found_plan_name' => $plan?->name,
                'found_duration_id' => $duration?->id,
                'found_duration_days' => $duration?->days
            ]);
        }

        if (!$plan || !$duration) {
            Log::error('Plan or duration not found', [
                'subscription_id' => $subscription->id,
                'plan_found' => $plan ? true : false,
                'duration_found' => $duration ? true : false,
                'requested_plan_id' => $planId,
                'requested_duration_id' => $durationId
            ]);
            return null;
        }

        $status = $enabled ?
            SubscriptionStatusEnum::ACTIVE->value :
            SubscriptionStatusEnum::BLOCKED->value;

        $endDatetime = SubscriptionHelper::calculateNewEndDate($subscription, $duration, $enabled);

        Log::info('Subscription update parameters calculated', [
            'subscription_id' => $subscription->id,
            'new_plan_id' => $plan->id,
            'new_duration_id' => $duration->id,
            'new_status' => $status,
            'new_end_datetime' => $endDatetime,
            'current_end_datetime' => $subscription->end_datetime
        ]);

        $updatedSubscription = app(UpdateSubscriptionAction::class)->execute(
            new UpdateSubscriptionActionDto(
                planId: $plan->id,
                durationId: $duration->id,
                status: $status,
                endDatetime: $endDatetime
            ),
            $subscription
        );

        Log::info('SubscriptionService::update completed', [
            'subscription_id' => $subscription->id,
            'update_success' => (bool)$updatedSubscription,
            'result_subscription_id' => $updatedSubscription?->id,
            'result_status' => $updatedSubscription?->status?->value
        ]);

        // Лотерейные билеты теперь начисляются в PaymentService при успешной оплате
        // Убрано дублирующее начисление билетов

        return $updatedSubscription;
    }

    private function createReferralCodeAndBonusAccount(Subscription $subscription): void
    {
        try {
            $referralCode = ReferralCode::getOrCreateForSubscription($subscription);
            $bonusAccount = BonusAccount::getOrCreateForSubscription($subscription);

            Log::info('Referral code and bonus account created for subscription', [
                'subscription_id' => $subscription->id,
                'referral_code' => $referralCode->code
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create referral code and bonus account for subscription', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    private function createReferralData(Subscription $subscription): void
    {
        try {
            $referralCode = ReferralCode::getOrCreateForSubscription($subscription);
            $bonusAccount = BonusAccount::getOrCreateForSubscription($subscription);

            // Создаем лотерейные билеты за подписку (только для платных подписок)
            if ($subscription->plan->price > 0) {
                $this->createLotteryTicketsForSubscription($subscription);
            }

            Log::info('Referral data created for subscription', [
                'subscription_id' => $subscription->id,
                'referral_code' => $referralCode->code
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create referral data for subscription', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    private function createLotteryTicketsForSubscription(Subscription $subscription): void
    {
        try {
            // Получаем количество месяцев из duration (конвертируем дни в месяцы, примерно 30 дней = 1 месяц)
            $days = $subscription->duration->days ?? 30;
            $months = max(1, round($days / 30));

            // Создаем билеты за подписку
            $ticketsCreated = $this->lotteryTicketService->createTicketsForSubscription($subscription, $months);

            Log::info('Lottery tickets created for subscription', [
                'subscription_id' => $subscription->id,
                'months' => $months,
                'tickets_created' => $ticketsCreated
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create lottery tickets for subscription', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
