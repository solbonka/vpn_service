<?php

namespace App\Jobs\Subscription\Blocked;

class BlockedSubscriptionMessage
{
    public static function messagePayment(): string
    {
        return "
Ваш VPN-ключ заблокирован. 😢

Ключ был заблокирован и сейчас недоступен для использования.
Если вы хотите снова пользоваться VPN, нажмите на кнопку
'🔄️ Продлить подписку' ниже или в главном меню
";
    }

    public static function messageNotPayment(): string
    {
        return "
Ваш VPN-ключ заблокирован. 😢

Ключ был заблокирован и сейчас недоступен для использования.
Если вы хотите снова пользоваться VPN, нажмите на кнопку
'🔄️ Продлить подписку' в главном меню
";
    }
}
