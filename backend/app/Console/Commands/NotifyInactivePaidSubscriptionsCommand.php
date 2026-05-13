<?php

namespace App\Console\Commands;

use App\Jobs\Subscription\Inactive\NotifyInactivePaidSubscriptionJob;
use App\Models\Subscription;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class NotifyInactivePaidSubscriptionsCommand extends Command
{
    protected $signature = 'subscriptions:notify-inactive-paid';
    protected $description = 'Уведомляет о неактивных пользователях (оплатили подписку, использовали VPN, но не продлили)';

    public function handle(): int
    {
        $queryBlockedSubscriptions = Subscription::findBlockedForNotifications(false);

        if (!$queryBlockedSubscriptions->exists()) {
            $this->info('Неактивных пользователей (платные) не найдено.');

            return CommandAlias::SUCCESS;
        }

        $queryBlockedSubscriptions->chunk(100, function ($blockedSubscriptions) {
            $subscriptionIds = $blockedSubscriptions->pluck('id')->toArray();

            NotifyInactivePaidSubscriptionJob::dispatch($subscriptionIds)->onQueue('notify_inactive_paid');
        });

        $this->info('Задача по уведомлению о неактивных пользователях (платные) добавлена в очередь');

        return CommandAlias::SUCCESS;
    }
}

