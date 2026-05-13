<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Services\SyncVpnKey\SyncVpnKeyService;
use App\Services\Remnawave\SyncVpnKeyService as RemnawaveSyncVpnKeyService;
use Exception;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class SyncSubscriptionsCommand extends Command
{
    protected $signature = 'subscription:sync';
    protected $description = 'Блокировка просроченных подписок и ключей VPN';

    /**
     * @throws Exception
     */
    public function handle(): int
    {
        $telegraphChatId = 4109;

        $subscription = Subscription::query()->where('telegraph_chat_id', $telegraphChatId)->first();

        if (!$subscription) {
            $this->info("Подписка: $telegraphChatId не найдена");

            return 0;
        }

        $servers = app(SyncVpnKeyService::class, ['subscription' => $subscription])->handle();
        app(RemnawaveSyncVpnKeyService::class)->handle($subscription);

        if (!$servers) {
            $this->info("Не удалось синхронизировать VPN ключи для чата: $subscription->telegraph_chat_id");
            return 0;
        }


        $this->info('Задача по синхронизации подписок добавлена в очередь');

        return CommandAlias::SUCCESS;
    }
}
