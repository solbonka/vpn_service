<?php

namespace App\Enums\Referral;

enum ReferralBonusTypeEnum: string
{
    case RUBLES = 'rubles';
    case DAYS = 'days';
    case LOTTERY_TICKETS = 'lottery_tickets';

    public function getLabel(): string
    {
        return match($this) {
            self::RUBLES => 'Рубли',
            self::DAYS => 'Дни подписки',
            self::LOTTERY_TICKETS => 'Лотерейные билеты',
        };
    }

    public function getDescription(): string
    {
        return match($this) {
            self::RUBLES => 'Рубли на бонусный счет для оплаты подписки',
            self::DAYS => 'Дополнительные дни подписки',
            self::LOTTERY_TICKETS => 'Участие в лотерее с призами',
        };
    }

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}

