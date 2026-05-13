<?php

namespace App\Jobs\Chat\UpdateMenu;

use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Models\TelegraphChat;

class NotifyUpdateMenuToChatButtons
{
    public static function buttons(TelegraphChat $chat): Keyboard
    {
        return Keyboard::make()
            ->buttons([
                Button::make('🔄 Обновить меню')->action('updateMenuAction')
                    ->param('chat_id', $chat->id)
            ]);
    }
}
