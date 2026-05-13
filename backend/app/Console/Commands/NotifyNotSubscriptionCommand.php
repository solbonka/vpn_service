<?php

namespace App\Console\Commands;

use App\Jobs\Chat\NotSubscription\NotifyNotSubscriptionToChatJob;
use App\Models\CustomTelegraphChat;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class NotifyNotSubscriptionCommand extends Command
{
    protected $signature = 'chat:notify-not-subscription';
    protected $description = 'Уведомление об отсутствии подписки';

    public function handle(): int
    {
        $queryChatNotSubscription = CustomTelegraphChat::query()
            ->whereDoesntHave('subscriptions');

        if (! $queryChatNotSubscription->exists()) {

            $this->info('Нет доступных чатов для уведомления об отсутствии подписки.');

            return CommandAlias::SUCCESS;
        }

        $queryChatNotSubscription->chunk(100, function ($chats) {
            $chatIds = $chats->pluck('id')->toArray();

            NotifyNotSubscriptionToChatJob::dispatch($chatIds);
        });

        $this->info('Задача по уведомлению об отсутствии подписки добавлена в очередь');

        return CommandAlias::SUCCESS;
    }
}
