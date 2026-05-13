<?php

namespace Database\Seeders;

use App\Enums\Referral\ReferralBonusTypeEnum;
use App\Models\BonusType;
use Illuminate\Database\Seeder;

class BonusTypeSeeder extends Seeder
{
    public function run(): void
    {
        $bonusTypes = [
            [
                'name' => 'Пополнение баланса',
                'type' => ReferralBonusTypeEnum::RUBLES,
                'amount' => 50,
                'is_active' => true,
                'description' => '50 рублей на бонусный счет для оплаты подписки'
            ],
            [
                'name' => 'Дополнительные дни',
                'type' => ReferralBonusTypeEnum::DAYS,
                'amount' => 7,
                'is_active' => false,
                'description' => '7 дополнительных дней подписки'
            ],
            [
                'name' => 'Лотерейный билет',
                'type' => ReferralBonusTypeEnum::LOTTERY_TICKETS,
                'amount' => 1,
                'is_active' => false,
                'description' => '1 лотерейный билет для участия в розыгрыше призов'
            ]
        ];

        foreach ($bonusTypes as $bonusType) {
            BonusType::create($bonusType);
        }
    }
}
