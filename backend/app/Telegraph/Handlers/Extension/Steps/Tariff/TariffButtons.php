<?php

namespace App\Telegraph\Handlers\Extension\Steps\Tariff;

use App\Models\Plan;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;

class TariffButtons
{
    public static function buttons(array $buttons): Keyboard
    {
        return Keyboard::make()->buttons($buttons);
    }

    public static function buttonPlan(Plan $plan, bool $isExtension): Button
    {
        return Button::make("$plan->name: $plan->price руб. в месяц")
            ->action('selectDurationAction')
            ->param('plan_id', $plan->id)
            ->param('is_extension', $isExtension);
    }
}
