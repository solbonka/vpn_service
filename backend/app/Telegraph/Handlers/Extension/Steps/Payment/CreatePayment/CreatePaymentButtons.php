<?php

namespace App\Telegraph\Handlers\Extension\Steps\Payment\CreatePayment;

use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;

class CreatePaymentButtons
{
    public static function buttons(string $link, string $paymentId, string $data): Keyboard
    {
        return Keyboard::make()
            ->row([
                Button::make('💳 Оплатить')->url($link),
            ])
            ->row([
                Button::make('💸 Попросить оплатить')->action('requestSharePaymentAction')
                    ->param('payment_id', $paymentId)
            ])
            ->row([
                Button::make('◀️ Назад')->action('selectPaymentMethodAction')
                    ->param('data', $data)
            ]);
    }
}
