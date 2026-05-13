<?php

namespace App\Jobs\Subscription\Expired;

class NotifyExpiredSubscriptionMessage
{
    public static function messagePayment(): string
    {
        return "
📢 *Внимание!*

Ваша подписка на VPN истекла. ⏳

Чтобы снова получить защиту и свободный доступ в интернет, продлите подписку прямо сейчас!

Нажмите кнопку '🔄️ Продлить подписку' ниже или в главном меню, чтобы оформить новую подписку
";
    }

    public static function messageNotPayment(): string
    {
        return "
📢 *Внимание!*

Ваша подписка на VPN истекла. ⏳

Чтобы снова получить защиту и свободный доступ в интернет, продлите подписку прямо сейчас!

Нажмите кнопку '🔄️ Продлить подписку' в главном меню, чтобы оформить новую подписку
";
    }
}
