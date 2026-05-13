<?php

namespace App\Telegraph\Handlers\Extension\Steps\Payment\SelectPayment;

use App\Models\Duration;
use App\Models\Plan;

class SelectPaymentMessage
{
    public static function messageBlockedUser(Plan $plan, Duration $duration, float $discountedPrice): string
    {
        return "
✅ Тариф: *{$plan->name}* на *{$duration->name}* ({$duration->days} дней)

💳 Сумма к оплате: *{$discountedPrice} руб.*

Пожалуйста, выберите удобный способ оплаты:
";
    }

    public static function messageActiveUser(
        Plan $plan,
        Duration $duration,
        string $endDate
    ): string
    {
        $hasMultiplePaidPlans = $plan::paidWithActiveServers()->count() > 1;

        if ($hasMultiplePaidPlans) {
            $buttonName = '*«Сменить тариф»*';
            $icon = '📋';
        } else {
            $buttonName = '*«Сменить длительность»*';
            $icon = '⏳';
        }

        return "
🎫 *Ваш текущий тариф: $plan->name*

⏳ *Срок действия:* $duration->name
📅 *Подписка активна до:* $endDate

🔄 Если вы хотите продлить подписку, просто выберите удобный способ оплаты ниже.

$icon Или нажмите $buttonName, чтобы изменить условия.
";
    }
}
