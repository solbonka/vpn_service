<?php

namespace App\Telegraph\Handlers\Extension\Steps\Payment\CreatePayment;

class CreatePaymentMessage
{
    public static function message(): string
    {
        return "
✨ Чтобы продолжить и перейти к оплате, нажмите на кнопку *«Оплатить»* и перейдите по *ссылке*. Или попросите оплатить подписку за вас.
";
    }
}
