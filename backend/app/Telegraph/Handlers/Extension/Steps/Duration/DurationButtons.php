<?php

namespace App\Telegraph\Handlers\Extension\Steps\Duration;

use App\Helpers\Params\ParamsHelper;
use App\Models\Duration;
use App\Models\Plan;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;

class DurationButtons
{
    public static function buttons(Plan $plan, array $buttons, bool $isExtension): Keyboard
    {
        $keyboard = Keyboard::make()->buttons($buttons);

        $hasMultiplePaidPlans = $plan::paidWithActiveServers()->count() > 1;

        if ($hasMultiplePaidPlans) {
            $keyboard->row([
                Button::make('◀️ Назад')->action('selectTariffAction')
                    ->param('is_extension', $isExtension)
            ]);
        }

        return $keyboard;
    }

    public static function buttonDuration(
        Duration $duration,
        string   $priceText,
        Plan     $plan,
        bool     $isExtension
    ): Button
    {
        $params = [
            'duration_id' => $duration->id,
            'plan_id' => $plan->id,
            'is_extension' => $isExtension
        ];

        return Button::make("$duration->name — $duration->days дней, $priceText")
            ->action('selectPaymentMethodAction')
            ->param('data', ParamsHelper::encode($params));
    }
}
