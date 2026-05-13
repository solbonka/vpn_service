<?php

namespace Database\Seeders;

use App\Enums\Lottery\LotteryTicketSourceEnum;
use App\Enums\Payment\PaymentStatusEnum;
use App\Enums\Referral\ReferralBonusTypeEnum;
use App\Enums\Subscription\SubscriptionStatusEnum;
use App\Models\BonusAccount;
use App\Models\BonusType;
use App\Models\CustomTelegraphChat;
use App\Models\LotteryTicket;
use App\Models\Payment;
use App\Models\ReferralCode;
use App\Models\Subscription;
use App\Models\Plan;
use App\Models\Duration;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ReferralAnalyticsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Создаем бонусный тип для рефералов
        $bonusType = BonusType::create([
            'name' => 'Лотерейные билеты за реферала',
            'type' => ReferralBonusTypeEnum::LOTTERY_TICKETS,
            'amount' => 2,
            'is_active' => true,
            'description' => '2 лотерейных билета за каждого привлеченного реферала'
        ]);

        // Создаем план и длительность
        $plan = Plan::firstOrCreate([
            'name' => 'VIP Plan',
            'price' => 500
        ]);

        $duration = Duration::where('days', 30)->first();
        if (!$duration) {
            $duration = Duration::create([
                'name' => '1 месяц',
                'days' => 30,
                'discount_percentage' => 0,
                'is_trial' => false
            ]);
        }

        // Создаем бота
        $bot = \DefStudio\Telegraph\Models\TelegraphBot::firstOrCreate([
            'token' => 'test_bot_token',
            'name' => 'Test Bot'
        ]);

        // Создаем тестовые чаты
        $chats = [];
        for ($i = 1; $i <= 20; $i++) {
            $chats[] = CustomTelegraphChat::create([
                'chat_id' => 1000000000 + $i,
                'first_name' => "User{$i}",
                'last_name' => "Test{$i}",
                'name' => "User{$i} Test{$i}",
                'telegraph_bot_id' => $bot->id,
                'client_operating_system_id' => 1
            ]);
        }

        // Создаем подписки
        $subscriptions = [];
        foreach ($chats as $index => $chat) {
            $subscriptions[] = Subscription::create([
                'telegraph_chat_id' => $chat->id,
                'plan_id' => $plan->id,
                'duration_id' => $duration->id,
                'status' => SubscriptionStatusEnum::ACTIVE,
                'end_datetime' => Carbon::now()->addDays(30),
                'token' => 'test_token_' . $index,
                'created_at' => Carbon::now()->subDays(rand(1, 30))
            ]);
        }

        // Создаем реферальные коды для первых 10 пользователей
        $referralCodes = [];
        for ($i = 0; $i < 10; $i++) {
            $referralCodes[] = ReferralCode::create([
                'subscription_id' => $subscriptions[$i]->id,
                'code' => 'REF' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'is_active' => true,
                'created_at' => Carbon::now()->subDays(rand(1, 25))
            ]);
        }

        // Создаем бонусные счета
        foreach ($subscriptions as $subscription) {
            BonusAccount::create([
                'subscription_id' => $subscription->id,
                'balance_rubles' => rand(0, 1000),
                'balance_days' => rand(0, 30),
                'balance_lottery_tickets' => rand(0, 20)
            ]);
        }

        // Создаем реферальные связи (последние 10 пользователей приглашены первыми 10)
        for ($i = 10; $i < 20; $i++) {
            $referrerIndex = $i - 10;
            $subscriptions[$i]->update([
                'referred_by_code_id' => $referralCodes[$referrerIndex]->id
            ]);
        }

        // Создаем платежи
        foreach ($subscriptions as $index => $subscription) {
            $paymentCount = rand(1, 3);
            for ($j = 0; $j < $paymentCount; $j++) {
                Payment::create([
                    'subscription_id' => $subscription->id,
                    'amount' => $plan->price,
                    'status' => PaymentStatusEnum::SUCCEEDED,
                    'yookassa_payment_id' => 'test_payment_' . $subscription->id . '_' . $j,
                    'currency' => 'RUB',
                    'created_at' => Carbon::now()->subDays(rand(1, 20))
                ]);
            }
        }

        // Создаем лотерейные билеты
        foreach ($subscriptions as $subscription) {
            // Билеты за подписку
            $subscriptionTickets = rand(1, 3);
            for ($i = 0; $i < $subscriptionTickets; $i++) {
                LotteryTicket::create([
                    'subscription_id' => $subscription->id,
                    'ticket_number' => rand(1, 9999),
                    'source_type' => LotteryTicketSourceEnum::SUBSCRIPTION_PAYMENT,
                    'source_id' => $subscription->id,
                    'created_at' => Carbon::now()->subDays(rand(1, 15))
                ]);
            }

            // Билеты за рефералов (только для первых 10 пользователей)
            if ($subscription->referred_by_code_id) {
                $referralTickets = rand(1, 4);
                for ($i = 0; $i < $referralTickets; $i++) {
                    LotteryTicket::create([
                        'subscription_id' => $subscription->id,
                        'ticket_number' => rand(1, 9999),
                        'source_type' => LotteryTicketSourceEnum::REFERRAL_BONUS,
                        'source_id' => $subscription->referred_by_code_id,
                        'created_at' => Carbon::now()->subDays(rand(1, 10))
                    ]);
                }
            }
        }

        // Создаем дополнительные данные для разных периодов
        $this->createHistoricalData($subscriptions, $referralCodes, $plan, $duration, $bot);

        $this->command->info('Referral analytics test data created successfully!');
        $this->command->info('Created:');
        $this->command->info('- 20 subscriptions');
        $this->command->info('- 10 referral codes');
        $this->command->info('- 20 bonus accounts');
        $this->command->info('- 10 referral relationships');
        $this->command->info('- Multiple payments');
        $this->command->info('- Multiple lottery tickets');
    }

    private function createHistoricalData($subscriptions, $referralCodes, $plan, $duration, $bot)
    {
        // Создаем данные за последние 6 месяцев для графиков
        for ($month = 1; $month <= 6; $month++) {
            $date = Carbon::now()->subMonths($month);

            // Создаем несколько подписок в каждом месяце
            for ($i = 0; $i < rand(2, 5); $i++) {
                $chat = TelegraphChat::create([
                    'chat_id' => 2000000000 + ($month * 100) + $i,
                    'first_name' => "HistUser{$month}_{$i}",
                    'last_name' => "HistTest{$month}_{$i}",
                    'name' => "HistUser{$month}_{$i} HistTest{$month}_{$i}",
                    'telegraph_bot_id' => $bot->id,
                    'client_operating_system_id' => 1
                ]);

                $subscription = Subscription::create([
                    'telegraph_chat_id' => $chat->id,
                    'plan_id' => $plan->id,
                    'duration_id' => $duration->id,
                    'status' => SubscriptionStatusEnum::ACTIVE,
                    'end_datetime' => $date->copy()->addDays(30),
                    'token' => 'hist_token_' . $month . '_' . $i,
                    'created_at' => $date->copy()->addDays(rand(1, 28))
                ]);

                // 50% шанс быть рефералом
                if (rand(0, 1) && count($referralCodes) > 0) {
                    $referralCode = $referralCodes[array_rand($referralCodes)];
                    $subscription->update([
                        'referred_by_code_id' => $referralCode->id
                    ]);

                    // Создаем платеж
                    Payment::create([
                        'subscription_id' => $subscription->id,
                        'amount' => $plan->price,
                        'status' => PaymentStatusEnum::SUCCEEDED,
                        'yookassa_payment_id' => 'hist_payment_' . $subscription->id,
                        'currency' => 'RUB',
                        'created_at' => $date->copy()->addDays(rand(1, 5))
                    ]);
                }
            }
        }
    }
}

