<?php

namespace Database\Seeders;

use App\Models\Server;
use App\Models\ServerMetric;
use App\Models\Subscription;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Duration;
use App\Models\CustomTelegraphChat;
use DefStudio\Telegraph\Models\TelegraphBot;
use App\Enums\Subscription\SubscriptionStatusEnum;
use App\Enums\Payment\PaymentStatusEnum;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ChartTestDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Создание тестовых данных для графиков...');

        // Получаем или создаем бота
        $bot = TelegraphBot::first();
        if (!$bot) {
            $bot = TelegraphBot::create([
                'token' => 'test_bot_token',
                'name' => 'Test Bot',
            ]);
        }

        // Получаем или создаем сервер
        $server = Server::first();
        if (!$server) {
            $server = Server::create([
                'name' => 'Test Server',
                'code' => 'test-server',
                'is_active' => true,
                'order' => 1
            ]);
        }

        // Получаем или создаем план
        $plan = Plan::first();
        if (!$plan) {
            $plan = Plan::create([
                'name' => 'Premium',
                'price' => 500,
                'is_active' => true
            ]);
        }

        // Получаем или создаем длительность
        $duration = Duration::first();
        if (!$duration) {
            $duration = Duration::create([
                'name' => '1 месяц',
                'days' => 30,
                'is_trial' => false
            ]);
        }

        // Создаем тестовые данные за последние 30 дней
        $this->createServerMetrics($server);
        $this->createSubscriptions($plan, $duration, $bot);
        $this->createPayments();

        $this->command->info('Тестовые данные для графиков созданы успешно!');
    }

    private function createServerMetrics(Server $server): void
    {
        $this->command->info('Создание метрик сервера...');

        $baseUsers = 4000;
        $baseActive = 2500;
        $baseOnline = 200;

        for ($i = 30; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->startOfDay();
            $this->command->info("Creating data for date: " . $date->format('Y-m-d'));
            
            // Создаем несколько записей в день (каждые 4 часа)
            for ($hour = 0; $hour < 24; $hour += 4) {
                $timestamp = $date->copy()->addHours($hour)->addMinutes(rand(0, 59));
                
                // Добавляем случайные колебания
                $totalVariation = rand(-50, 100);
                $activeVariation = rand(-30, 80);
                $onlineVariation = rand(-20, 50);
                
                // Учитываем время суток (больше активности вечером)
                $timeMultiplier = $hour >= 18 ? 1.2 : ($hour >= 6 ? 1.0 : 0.8);
                
                $totalUsers = max(0, $baseUsers + $totalVariation + ($i * 20));
                $activeUsers = max(0, $baseActive + $activeVariation + ($i * 15));
                $onlineUsers = max(0, (int)(($baseOnline + $onlineVariation + ($i * 5)) * $timeMultiplier));

                ServerMetric::create([
                    'server_id' => $server->id,
                    'total_users' => $totalUsers,
                    'active_users' => $activeUsers,
                    'online_users' => $onlineUsers,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);
            }
        }
    }

    private function createSubscriptions(Plan $plan, Duration $duration, TelegraphBot $bot): void
    {
        $this->command->info('Создание подписок...');

        $statuses = [
            SubscriptionStatusEnum::ACTIVE,
            SubscriptionStatusEnum::BLOCKED
        ];

        $statusWeights = [80, 20]; // 80% активных, 20% заблокированных

        for ($i = 30; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            
            // Создаем 1-5 подписок в день
            $subscriptionsCount = rand(1, 5);
            
            for ($j = 0; $j < $subscriptionsCount; $j++) {
                $status = $this->getRandomWeightedStatus($statuses, $statusWeights);

                // Создаем уникальный чат для каждой подписки
                $chat = CustomTelegraphChat::create([
                    'chat_id' => 'test_chat_' . $i . '_' . $j,
                    'name' => 'Test User ' . $i . '_' . $j,
                    'telegraph_bot_id' => $bot->id,
                ]);

                $subscription = Subscription::create([
                    'token' => 'test_token_' . $i . '_' . $j,
                    'telegraph_chat_id' => $chat->id,
                    'plan_id' => $plan->id,
                    'duration_id' => $duration->id,
                    'status' => $status,
                    'end_datetime' => $date->copy()->addDays(30),
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
            }
        }
    }

    private function createPayments(): void
    {
        $this->command->info('Создание платежей...');

        $subscriptions = Subscription::where('status', SubscriptionStatusEnum::ACTIVE)->get();
        $amounts = [500, 1000, 1500, 2000, 2500]; // Различные суммы платежей

        for ($i = 30; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            
            // Создаем 0-8 платежей в день (больше в выходные)
            $isWeekend = $date->isWeekend();
            $paymentsCount = $isWeekend ? rand(3, 8) : rand(0, 5);
            
            for ($j = 0; $j < $paymentsCount; $j++) {
                $subscription = $subscriptions->random();
                $amount = $amounts[array_rand($amounts)];
                
                // 90% успешных платежей, 10% отмененных
                $status = rand(1, 10) <= 9 ? PaymentStatusEnum::SUCCEEDED : PaymentStatusEnum::CANCELED;
                
                Payment::create([
                    'subscription_id' => $subscription->id,
                    'yookassa_payment_id' => 'test_payment_' . $i . '_' . $j,
                    'status' => $status,
                    'amount' => $amount,
                    'currency' => 'RUB',
                    'created_at' => $date->copy()->addHours(rand(0, 23)),
                    'updated_at' => $date->copy()->addHours(rand(0, 23)),
                ]);
            }
        }
    }

    private function getRandomWeightedStatus(array $statuses, array $weights): SubscriptionStatusEnum
    {
        $totalWeight = array_sum($weights);
        $random = rand(1, $totalWeight);
        
        $currentWeight = 0;
        foreach ($statuses as $index => $status) {
            $currentWeight += $weights[$index];
            if ($random <= $currentWeight) {
                return $status;
            }
        }
        
        return $statuses[0]; // Fallback
    }
}
