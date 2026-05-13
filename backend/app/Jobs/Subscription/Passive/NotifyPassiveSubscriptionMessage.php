<?php

namespace App\Jobs\Subscription\Passive;

use App\Models\CustomTelegraphChat;

class NotifyPassiveSubscriptionMessage
{
    public static function message(CustomTelegraphChat $chat): string
    {
        $username = $chat->name ? '@' . trim(str_replace('[private]', '', $chat->name)) : null;
        $telegramId = $chat->chat_id;
        $firstName = $chat->first_name ?: '-';
        $lastName = $chat->last_name ?: '-';

        $createdAt = $chat->created_at;
        $hoursSinceRegistration = (int) abs($createdAt->diffInHours(now()));

        $adminUrl = config('app.admin_url');
        $chatDetailUrl = "{$adminUrl}/admin/chats/{$chat->id}";

        $message = "<b>⚠️ Клиент зашел в бота, но не стал подключаться</b>\n\n";
        $message .= "<b>Telegram ID:</b> {$telegramId}\n";
        $message .= "<b>Никнейм:</b> " . ($username ?: '-') . "\n";
        $message .= "<b>Имя:</b> {$firstName}\n";
        $message .= "<b>Фамилия:</b> {$lastName}\n";
        $message .= "<b>Прошло часов с момента регистрации:</b> {$hoursSinceRegistration}\n\n";
        $message .= "📊 <a href=\"{$chatDetailUrl}\">Посмотреть детали чата</a>";

        return $message;
    }
}
