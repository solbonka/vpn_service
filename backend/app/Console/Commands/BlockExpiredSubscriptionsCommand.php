<?php

namespace App\Console\Commands;

use App\Jobs\Subscription\Blocked\BlockedSubscriptionJob;
use App\Models\Subscription;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class BlockExpiredSubscriptionsCommand extends Command
{
    protected $signature = 'subscriptions:block-expired';
    protected $description = 'Блокировка просроченных подписок и ключей VPN';

    public function handle(): int
    {
        $querySubscriptionExpired = Subscription::findExpired();

        if (! $querySubscriptionExpired->exists()) {
            $this->info('Просроченных подписок не найдено.');

            return CommandAlias::SUCCESS;
        }

        $querySubscriptionExpired->chunk(100, function ($subscriptionExpired) {
            $subscriptionExpiredIds = $subscriptionExpired->pluck('id')->toArray();

            BlockedSubscriptionJob::dispatch($subscriptionExpiredIds)->onQueue('blocked_sub');
        });

        $this->info('Задача по блокировке подписок добавлена в очередь');

        return CommandAlias::SUCCESS;
    }
}
