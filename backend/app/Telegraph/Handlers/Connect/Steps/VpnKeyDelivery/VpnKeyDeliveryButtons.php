<?php

namespace App\Telegraph\Handlers\Connect\Steps\VpnKeyDelivery;

use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;

class VpnKeyDeliveryButtons
{
    public static function buttonsToV2RayTun(Keyboard $keyboard, string $link, int $osId): Keyboard
    {
        return $keyboard->buttons([
            Button::make('🚀 Подключить автоматически')->url($link),
            Button::make('📝 Пошаговая инструкция')->action('showConnectToV2RayTunGuideAction'),
            Button::make('◀️ Назад')->action('setupAppAction')->param('os_id', $osId)
        ]);
    }

    public static function buttonsToHapp(Keyboard $keyboard, string $link, int $osId): Keyboard
    {
        return $keyboard->buttons([
            Button::make('🚀 Подключить автоматически')->url($link),
            Button::make('📝 Пошаговая инструкция')->action('showConnectToHappGuideAction'),
            Button::make('◀️ Назад')->action('setupAppAction')->param('os_id', $osId)
        ]);
    }

    public static function buttonsToOtherApp(Keyboard $keyboard, string $domain, int $osId): Keyboard
    {
        return $keyboard->buttons([
            Button::make('🪟 Инструкция для Windows')->url($domain . '/instructions/connection/windows'),
            Button::make('💻 Инструкция для Mac')->url($domain . '/instructions/connection/mac'),
            Button::make('◀️ Назад')->action('setupApp')->param('os_id', $osId)
        ]);
    }
}
