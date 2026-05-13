<?php

namespace App\Telegraph\Handlers\Status;

use App\Enums\Subscription\SubscriptionStatusEnum;
use App\Models\Subscription;

class StatusMessage
{
    public static function messageSubscription(
        string $formattedEndDate,
        int    $daysLeft,
        string $manualImportUrl
    ): string
    {
        return "
Статус подписки: ✅ Активна

📅 Действует до: $formattedEndDate

⏳ Осталось дней: $daysLeft

Ваш ключ (скопируется при нажатии на ссылку):

`$manualImportUrl`

Нажмите на ссылку, чтобы скопировать⬆️

        ";
    }

    public static function messageNotSubscription(?Subscription $subscription): string
    {
        if ($subscription && $subscription->status === SubscriptionStatusEnum::BLOCKED) {
            $nameButton = '*«Продлить подписку»*';
        } else {
            $nameButton = '*«Подключиться»*';
        }

        return "
Статус подписки: ❎ У вас нет активного VPN-ключа.

Чтобы получить VPN ключ нажмите на кнопку $nameButton
";
    }
}
