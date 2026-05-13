<?php

namespace App\Console\Commands;

use App\Enums\Subscription\SubscriptionStatusEnum;
use App\Jobs\Subscription\Expired\NotifyExpiredSubscriptionJob;
use App\Models\Subscription;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class NotifyExpiredSubscriptionsCommand extends Command
{
    protected $signature = 'subscriptions:notify-expired';
    protected $description = 'Напоминает пользователям об истекшего срока подписки';

    public function handle(): int
    {
        $querySubscriptionBlocked = Subscription::query()->where('status', SubscriptionStatusEnum::BLOCKED);

        if (! $querySubscriptionBlocked->exists()) {
            $this->info('Истекших подписок не найдено.');

            return CommandAlias::SUCCESS;
        }

        $querySubscriptionBlocked->chunk(100, function ($subscriptionBlocked) {
            $subscriptionBlockedIds = $subscriptionBlocked->pluck('id')->toArray();

            NotifyExpiredSubscriptionJob::dispatch($subscriptionBlockedIds)->onQueue('notify_expired_sub');
        });

        $this->info('Задача по уведомлению об истекшем сроке подписок добавлена в очередь');

        return CommandAlias::SUCCESS;
    }
}
