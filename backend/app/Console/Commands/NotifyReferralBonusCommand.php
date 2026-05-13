<?php

namespace App\Console\Commands;

use App\Models\BonusAccount;
use App\Models\ReferralCode;
use App\Models\Subscription;
use Illuminate\Console\Command;

class NotifyReferralBonusCommand extends Command
{
    protected $signature = 'referral:notify 
                            {subscription-id : Subscription ID to notify}
                            {--bonus-type= : Specific bonus type to notify about}';

    protected $description = 'Send notification about referral bonus to user';

    public function handle(): int
    {
        $subscriptionId = $this->argument('subscription-id');
        $bonusType = $this->option('bonus-type');

        $subscription = Subscription::with(['telegraphChat', 'referralCodes.bonusType', 'bonusAccount'])
            ->find($subscriptionId);

        if (!$subscription) {
            $this->error("Subscription with ID {$subscriptionId} not found");
            return 1;
        }

        if (!$subscription->telegraphChat) {
            $this->error("No Telegram chat found for subscription");
            return 1;
        }

        $bonusAccount = $subscription->bonusAccount;
        if (!$bonusAccount) {
            $this->error("No bonus account found for subscription");
            return 1;
        }

        $balance = $bonusAccount->getTotalBalance();
        $referralCode = $subscription->referralCodes->first();

        // Формируем сообщение
        $message = $this->buildNotificationMessage($balance, $referralCode, $bonusType);

        try {
            // Отправляем уведомление
            $subscription->telegraphChat->message($message)->send();
            
            $this->info("✅ Notification sent successfully!");
            $this->line("Message: " . substr($message, 0, 100) . "...");
            
        } catch (\Exception $e) {
            $this->error("❌ Failed to send notification: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function buildNotificationMessage(array $balance, ?ReferralCode $referralCode, ?string $bonusType): string
    {
        $message = "🎉 Уведомление о бонусах!\n\n";

        if ($bonusType) {
            // Уведомление о конкретном типе бонуса
            switch ($bonusType) {
                case 'rubles':
                    $message .= "💰 Вы получили {$balance['rubles']} рублей на бонусный счет!\n\n";
                    $message .= "Эти средства можно использовать для оплаты подписки.\n\n";
                    break;
                    
                case 'days':
                    $message .= "📅 Вы получили {$balance['days']} дополнительных дней подписки!\n\n";
                    $message .= "Ваша подписка автоматически продлена.\n\n";
                    break;
                    
                case 'lottery_tickets':
                    $message .= "🎫 Вы получили {$balance['lottery_tickets']} лотерейных билетов!\n\n";
                    $message .= "Участвуйте в розыгрыше призов!\n\n";
                    break;
            }
        } else {
            // Общее уведомление о балансе
            $message .= "📊 Ваш текущий бонусный баланс:\n\n";
            
            if ($balance['rubles'] > 0) {
                $message .= "💰 Рублей: {$balance['rubles']}\n";
            }
            
            if ($balance['days'] > 0) {
                $message .= "📅 Дней: {$balance['days']}\n";
            }
            
            if ($balance['lottery_tickets'] > 0) {
                $message .= "🎫 Лотерейных билетов: {$balance['lottery_tickets']}\n";
            }
            
            $message .= "\n";
        }

        if ($referralCode) {
            $message .= "🔗 Ваша реферальная ссылка:\n";
            $message .= $referralCode->getReferralLink() . "\n\n";
            $message .= "Приглашайте друзей и получайте бонусы!\n";
        }

        $message .= "\n🚀 Спасибо за использование нашего VPN сервиса!";

        return $message;
    }
}
