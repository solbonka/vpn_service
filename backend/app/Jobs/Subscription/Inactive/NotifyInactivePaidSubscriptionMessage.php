<?php

namespace App\Jobs\Subscription\Inactive;

use App\Models\CustomTelegraphChat;
use App\Models\Subscription;

class NotifyInactivePaidSubscriptionMessage
{
    public static function message(Subscription $subscription, CustomTelegraphChat $chat): string
    {
        $username = $chat->name ? '@' . trim(str_replace('[private]', '', $chat->name)) : null;
        $telegramId = $chat->chat_id;
        $firstName = $chat->first_name ?: '-';
        $lastName = $chat->last_name ?: '-';

        $endDateTime = $subscription->end_datetime;
        $hoursAgo = (int) abs($endDateTime->diffInHours(now()));

        $adminUrl = config('app.admin_url');
        $chatDetailUrl = "{$adminUrl}/admin/chats/{$chat->id}";

        $message = "<b>⚠️ Пользователь оплатил подписку, использовал VPN, но затем не продлил подписку.</b>\n\n";
        $message .= "<b>Telegram ID:</b> {$telegramId}\n";
        $message .= "<b>Ник:</b> " . ($username ?: '-') . "\n";
        $message .= "<b>Имя:</b> {$firstName}\n";
        $message .= "<b>Фамилия:</b> {$lastName}\n";
        $message .= "<b>Прошло часов с момента истечения:</b> {$hoursAgo}\n\n";
        $message .= "📊 <a href=\"{$chatDetailUrl}\">Посмотреть детали чата</a>";

        return $message;
    }
}
