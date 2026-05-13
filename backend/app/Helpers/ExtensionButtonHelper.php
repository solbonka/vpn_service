<?php

namespace App\Helpers;

use App\Helpers\Params\ParamsHelper;
use App\Models\Plan;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;

class ExtensionButtonHelper
{
    public static function buttons(int $planId, int $durationId): Keyboard
    {
        $keyboard = Keyboard::make();

        $plan = Plan::query()->find($planId);
        $hasMultiplePaidPlans = Plan::paidWithActiveServers()->count() > 1;

        $button = Button::make('🔄️ Продлить подписку');

        if (! empty($plan->price)) {
            $params = [
                'duration_id' => $durationId,
                'plan_id' => $planId,
                'is_extension' => true
            ];

            $button->action('selectPaymentMethodAction')
                ->param('data', ParamsHelper::encode($params));
        } elseif ($hasMultiplePaidPlans) {
            $button->action('selectTariffAction');
        } else {
            $button->action('selectDurationAction');
        }

        $keyboard->buttons([$button]);

        return $keyboard;
    }
}
