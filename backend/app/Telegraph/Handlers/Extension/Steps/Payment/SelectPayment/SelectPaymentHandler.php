<?php

namespace App\Telegraph\Handlers\Extension\Steps\Payment\SelectPayment;

use App\Helpers\DeleteMessageHelper;
use App\Helpers\Params\ParamsHelper;
use App\Helpers\PricingHelper;
use App\Models\Duration;
use App\Models\Plan;
use App\Models\Subscription;
use Carbon\Carbon;
use DefStudio\Telegraph\DTO\CallbackQuery;
use DefStudio\Telegraph\Models\TelegraphChat;

class SelectPaymentHandler
{
    private TelegraphChat $chat;
    private CallbackQuery $callbackQuery;

    public function __construct(
        TelegraphChat $chat,
        CallbackQuery $callbackQuery
    )
    {
        $this->chat = $chat;
        $this->callbackQuery = $callbackQuery;
    }

    public function handle(): void
    {
        DeleteMessageHelper::delete($this->chat, $this->callbackQuery);

        $data = $this->callbackQuery->data();

        $dataDecode = ParamsHelper::decode($data['data']);

        $durationId  = $dataDecode['duration_id'] ?? null;
        $planId      = $dataDecode['plan_id'] ?? null;
        $isExtension = filter_var($dataDecode['is_extension'] ?? false, FILTER_VALIDATE_BOOLEAN);

        $plan = Plan::query()->find($planId);
        $duration = Duration::query()->find($durationId);

        $subscription = Subscription::query()->where('telegraph_chat_id', $this->chat->id)->first();

        $calculated = PricingHelper::calculateDiscountedPrice(
            $plan->price,
            $duration->days,
            $duration->discount_percentage
        );

        $discountedPrice = $calculated['discountedPrice'];

        if (! $isExtension) {
            $this->chat->message(SelectPaymentMessage::messageBlockedUser($plan, $duration, $discountedPrice))
                ->keyboard(SelectPaymentButtons::buttonsBlockedUser($plan, $duration, $isExtension, $data['data']))
                ->markdown()
                ->send();
        } else {
            $planSubscription = $subscription->plan;
            $durationSubscription = $subscription->duration;
            $endDate = Carbon::parse($subscription->end_datetime)->format('d.m.Y');

            $this->chat->message(SelectPaymentMessage::messageActiveUser($planSubscription, $durationSubscription, $endDate))
                ->keyboard(SelectPaymentButtons::buttonsActiveUser(
                    $planSubscription,
                    $durationSubscription,
                    $isExtension,
                    $data['data']
                ))
                ->markdown()
                ->send();
        }
    }
}
