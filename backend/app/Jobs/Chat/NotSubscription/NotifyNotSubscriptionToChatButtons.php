<?php

namespace App\Jobs\Chat\NotSubscription;

use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Models\TelegraphChat;

class NotifyNotSubscriptionToChatButtons
{
    public static function buttons(TelegraphChat $chat): Keyboard
    {
        return Keyboard::make()
            ->buttons([
                Button::make('✅ Получить подписку')->action('storeSubscriptionAction')
                    ->param('chat_id', $chat->id)
            ]);
    }
}
