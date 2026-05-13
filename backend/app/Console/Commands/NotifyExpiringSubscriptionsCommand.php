<?php

namespace App\Console\Commands;

use App\Jobs\Subscription\Expiring\NotifyExpiringSubscriptionJob;
use App\Models\Subscription;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class NotifyExpiringSubscriptionsCommand extends Command
{
    protected $signature = 'subscriptions:notify-expiring';
    protected $description = 'Уведомляет пользователей об истечении срока подписок за сутки';

    public function handle(): int
    {
        $querySubscriptionExpiring = Subscription::findExpiring();

        if (! $querySubscriptionExpiring->exists()) {
            $this->info('Истекающих подписок завтра не найдено.');

            return CommandAlias::SUCCESS;
        }

        $querySubscriptionExpiring->chunk(100, function ($subscriptionExpiring) {
            $subscriptionExpiringIds = $subscriptionExpiring->pluck('id')->toArray();

            NotifyExpiringSubscriptionJob::dispatch($subscriptionExpiringIds)->onQueue('notify_expiring_sub');
        });

        $this->info('Задача по уведомлению об истечении срока подписок добавлена в очередь');

        return CommandAlias::SUCCESS;
    }
}
