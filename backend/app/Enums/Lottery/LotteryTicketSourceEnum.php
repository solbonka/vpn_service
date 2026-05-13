<?php

namespace App\Enums\Lottery;

enum LotteryTicketSourceEnum: string
{
    case SUBSCRIPTION_PAYMENT = 'subscription_payment';
    case REFERRAL_BONUS = 'referral_bonus';

    public function getLabel(): string
    {
        return match($this) {
            self::SUBSCRIPTION_PAYMENT => 'Оплата подписки',
            self::REFERRAL_BONUS => 'Реферальный бонус',
        };
    }

    public function getDescription(): string
    {
        return match($this) {
            self::SUBSCRIPTION_PAYMENT => 'Билет получен за оплату подписки',
            self::REFERRAL_BONUS => 'Билет получен за приглашение друга',
        };
    }
}
