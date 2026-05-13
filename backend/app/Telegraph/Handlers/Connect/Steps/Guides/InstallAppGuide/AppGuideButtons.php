<?php

namespace App\Telegraph\Handlers\Connect\Steps\Guides\InstallAppGuide;

use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;

class AppGuideButtons
{
    public static function buttons(string $domain, int $osId): Keyboard
    {
        return Keyboard::make()
            ->buttons([
                Button::make('🍏 Инструкция для iPhone/iPad')->url($domain . '/instructions/setup/ios'),
                Button::make('🤖 Инструкция для Android')->url($domain . '/instructions/setup/android'),
                Button::make('🌐 Инструкция для Huawei/Honor')->url($domain . '/instructions/setup/huawei'),
                Button::make('🪟 Инструкция для Windows')->url($domain . '/instructions/setup/windows'),
                Button::make('💻 Инструкция для Mac')->url($domain . '/instructions/setup/mac'),
                Button::make('📺 Инструкция для Android TV')->url($domain . '/instructions/setup/android_tv'),

                Button::make('▶️ Продолжить')->action('setKeyAction')
                    ->param('os_id', $osId)
            ]);
    }
}
