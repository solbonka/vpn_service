<?php

namespace App\Helpers;

use DefStudio\Telegraph\DTO\CallbackQuery;
use DefStudio\Telegraph\Models\TelegraphChat;

class DeleteMessageHelper
{
    public static function delete(TelegraphChat $chat, CallbackQuery $callbackQuery): void
    {
        $messageId = $callbackQuery->message()->id();
        $chat->deleteMessage($messageId)->send();
    }
}
