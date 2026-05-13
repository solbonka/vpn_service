<?php

namespace App\Telegraph\Handlers\Extension\Steps\Duration;

use App\Helpers\DeleteMessageHelper;
use App\Helpers\PricingHelper;
use App\Models\Duration;
use App\Models\Plan;
use DefStudio\Telegraph\DTO\CallbackQuery;
use DefStudio\Telegraph\Models\TelegraphChat;

class DurationHandler
{
    private TelegraphChat $chat;
    private ?CallbackQuery $callbackQuery;

    public function __construct(TelegraphChat $chat, ?CallbackQuery $callbackQuery = null)
    {
        $this->chat = $chat;
        $this->callbackQuery = $callbackQuery;
    }

    public function handle(): void
    {
        $planId = Plan::query()->where('name', 'Базовый')->value('id');
        $isExtension = false;

        if ($this->callbackQuery) {
            DeleteMessageHelper::delete($this->chat, $this->callbackQuery);

            $data = $this->callbackQuery->data();
            $planId = $data['plan_id'] ?? $planId;
            $isExtension = filter_var($data['is_extension'] ?? false,
                FILTER_VALIDATE_BOOLEAN
            );
        }


        $plan = Plan::query()->with('servers')->findOrFail($planId);
        $durations = Duration::query()->where('is_trial', false)->orderBy('id')->get();

        $buttons = [];

        foreach ($durations as $duration) {
            $calculated = PricingHelper::calculateDiscountedPrice(
                $plan->price,
                $duration->days,
                $duration->discount_percentage
            );

            $oldPrice = $calculated['oldPrice'];
            $discountedPrice = $calculated['discountedPrice'];
            $discountPercent = $calculated['discountPercent'];

            $priceText = "$oldPrice руб. ⮕ $discountedPrice руб. (скидка $discountPercent%)";

            $buttons[] = DurationButtons::buttonDuration(
                $duration,
                $priceText,
                $plan,
                $isExtension
            );
        }

        if (config('payment.enabled')) {
            $this->chat->message(DurationMessage::message())
                ->keyboard(DurationButtons::buttons($plan, $buttons, $isExtension))
                ->markdown()
                ->send();
        }
    }
}
