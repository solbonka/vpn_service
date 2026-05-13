<?php

namespace App\Telegraph\Handlers\Connect\Steps\Guides\ConnectKeyToAppGuide\V2RayTun;

use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;

class V2RayTunGuideButtons
{
    public static function buttons(string $domain): Keyboard
    {
        return Keyboard::make()
            ->buttons([
                Button::make('🍏 Инструкция для iPhone/iPad')->url($domain . '/instructions/connection/ios'),
                Button::make('🤖 Инструкция для Android')->url($domain . '/instructions/connection/android'),
            ]);
    }
}
