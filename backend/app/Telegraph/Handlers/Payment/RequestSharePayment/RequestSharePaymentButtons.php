<?php

namespace App\Telegraph\Handlers\Payment\RequestSharePayment;

use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;

class RequestSharePaymentButtons
{
    public static function buttons(
        string $shareUrl,
    ): Keyboard
    {
        return Keyboard::make()
            ->row([
                Button::make('📤 Поделиться')->url('https://t.me/share/url?url=' . urlencode($shareUrl) . '&text=' . urlencode('Помоги мне оплатить VPN подписку! 🙏')),
            ]);
    }
}

