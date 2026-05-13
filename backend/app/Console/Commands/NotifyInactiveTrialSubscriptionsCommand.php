<?php

namespace App\Console\Commands;

use App\Jobs\Subscription\Inactive\NotifyInactiveTrialSubscriptionJob;
use App\Models\Subscription;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class NotifyInactiveTrialSubscriptionsCommand extends Command
{
    protected $signature = 'subscriptions:notify-inactive-trial';
    protected $description = 'Уведомляет о неактивных пользователях (использовали пробный период, но не продлили)';

    public function handle(): int
    {
        $queryBlockedSubscriptions = Subscription::findBlockedForNotifications(true);

        if (!$queryBlockedSubscriptions->exists()) {
            $this->info('Неактивных пользователей (пробный период) не найдено.');

            return CommandAlias::SUCCESS;
        }

        $queryBlockedSubscriptions->chunk(100, function ($blockedSubscriptions) {
            $subscriptionIds = $blockedSubscriptions->pluck('id')->toArray();

            NotifyInactiveTrialSubscriptionJob::dispatch($subscriptionIds)->onQueue('notify_inactive_trial');
        });

        $this->info('Задача по уведомлению о неактивных пользователях (пробный период) добавлена в очередь');

        return CommandAlias::SUCCESS;
    }
}

