<?php

namespace App\Telegraph\Handlers\Extension\Steps\Payment\SelectPayment;

use App\Models\Duration;
use App\Models\Plan;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;

class SelectPaymentButtons
{
    public static function buttonsBlockedUser(
        Plan $plan,
        Duration $duration,
        bool $isExtension,
        string $data
    ): Keyboard
    {
        return Keyboard::make()
            ->row([
                Button::make('💳 Оплатить картой')
                    ->action('createPaymentAction')
                    ->param('plan_id', $plan->id)
                    ->param('duration_id', $duration->id)
                    ->param('data', $data)
            ])
            ->row([
                Button::make('🎫 Ввести промокод')
                    ->action('enterPromoCodeAction')
                    ->param('plan_id', $plan->id)
                    ->param('duration_id', $duration->id)
                    ->param('data', $data)
            ])
            ->row([
                Button::make('◀️ Назад')
                    ->action('selectDurationAction')
                    ->param('plan_id', $plan->id)
                    ->param('is_extension', $isExtension)
            ]);
    }

    public static function buttonsActiveUser(
        Plan $plan,
        Duration $duration,
        bool $isExtension,
        string $data
    ): Keyboard
    {
        $keyboard = Keyboard::make()
            ->row([
                Button::make('💳 Оплатить картой')
                    ->action('createPaymentAction')
                    ->param('plan_id', $plan->id)
                    ->param('duration_id', $duration->id)
                    ->param('data', $data)
            ])
            ->row([
                Button::make('🎫 Ввести промокод')
                    ->action('enterPromoCodeAction')
                    ->param('plan_id', $plan->id)
                    ->param('duration_id', $duration->id)
                    ->param('data', $data)
            ]);

        $hasMultiplePaidPlans = $plan::paidWithActiveServers()->count() > 1;

        if ($hasMultiplePaidPlans) {
            $keyboard->row([
                Button::make('📋 Сменить тариф')->action('selectTariffAction')
                    ->param('is_extension', $isExtension)
            ]);
        } else {
            $keyboard->row([
                Button::make('⏳ Сменить длительность')->action('selectDurationAction')
                    ->param('plan_id', $plan->id)
                    ->param('is_extension', $isExtension)
            ]);
        }

        return $keyboard;
    }
}
