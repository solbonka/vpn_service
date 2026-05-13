<?php

namespace App\Telegraph\Handlers\Connect\Steps\ChannelSubscription;

use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;

class ChannelSubscriptionButtons
{
    public static function buttons(string $chanelLink): Keyboard
    {
        return Keyboard::make()
            ->row([
                Button::make('Проверить подписку')->action('checkChannelSubscriptionAction'),
                Button::make('Подписаться')->url($chanelLink)])
            ->row([
                Button::make('◀️ В главное меню')->action('getMenuAction')
            ]);
    }
}
