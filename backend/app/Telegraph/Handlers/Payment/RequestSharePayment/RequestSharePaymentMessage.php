<?php

namespace App\Telegraph\Handlers\Payment\RequestSharePayment;

class RequestSharePaymentMessage
{
    public static function message(string $shareUrl): string
    {
        return "
Отправьте эту ссылку тому, кто оплатит вам подписку:

`{$shareUrl}`

Получатель сможет открыть её в любом браузере и оплатить. После оплаты ваша подписка активируется автоматически.
        ";
    }
}

