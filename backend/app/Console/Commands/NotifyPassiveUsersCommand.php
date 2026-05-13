<?php

namespace App\Console\Commands;

use App\Jobs\Subscription\Passive\NotifyPassiveSubscriptionJob;
use App\Models\Subscription;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class NotifyPassiveUsersCommand extends Command
{
    protected $signature = 'subscriptions:notify-passive';
    protected $description = 'Уведомляет о пассивных пользователях (не подключились через 3 часа после создания подписки)';

    public function handle(): int
    {
        $queryPassiveSubscriptions = Subscription::findPassiveForNotifications();

        if (!$queryPassiveSubscriptions->exists()) {
            $this->info('Пассивных пользователей не найдено.');

            return CommandAlias::SUCCESS;
        }

        $queryPassiveSubscriptions->chunk(100, function ($passiveSubscriptions) {
            $subscriptionIds = $passiveSubscriptions->pluck('id')->toArray();

            NotifyPassiveSubscriptionJob::dispatch($subscriptionIds)->onQueue('notify_passive_sub');
        });

        $this->info('Задача по уведомлению о пассивных пользователях добавлена в очередь');

        return CommandAlias::SUCCESS;
    }
}

