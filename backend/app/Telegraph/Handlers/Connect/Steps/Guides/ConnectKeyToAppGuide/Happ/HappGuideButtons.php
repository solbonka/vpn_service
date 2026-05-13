<?php

namespace App\Telegraph\Handlers\Connect\Steps\Guides\ConnectKeyToAppGuide\Happ;

use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;

class HappGuideButtons
{
    public static function buttons(string $domain): Keyboard
    {
        return Keyboard::make()
            ->buttons([
                Button::make('💻 Инструкция для Mac')->url($domain . '/instructions/connection/mac'),
                Button::make('📺 Инструкция для Android TV')->url($domain . '/instructions/connection/android_tv'),
                Button::make('🪟 Инструкция для Windows')->url($domain . '/instructions/connection/windows'),
            ]);
    }
}
