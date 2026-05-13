<?php

namespace App\Console\Commands;

use App\Jobs\Chat\UpdateMenu\NotifyUpdateMenuToChatJob;
use App\Models\CustomTelegraphChat;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class NotifyUpdateMenuCommand extends Command
{
    protected $signature = 'chat:notify-update-menu';
    protected $description = 'Уведомление об обновлении главного меню';

    public function handle(): int
    {
        $queryTelegraphChat = CustomTelegraphChat::query()
            ->where('created_at', '<=', '2025-07-28 15:00:00')
            ->whereHas('subscriptions', function($query) {
                $query->where('end_datetime', '<=', '2025-08-04 15:00:00');
            });

        if (! $queryTelegraphChat->exists()) {
            $this->info('Нет доступных чатов для уведомления.');
            return CommandAlias::SUCCESS;
        }

        $allChatIds = $queryTelegraphChat->pluck('id')->toArray();

        $this->info('Будут оповещены чаты с ID: ' . implode(', ', $allChatIds));
        $this->info('Всего чатов для оповещения: ' . count($allChatIds));

        $queryTelegraphChat->chunk(100, function ($chats) {
            $chatIds = $chats->pluck('id')->toArray();

            NotifyUpdateMenuToChatJob::dispatch($chatIds)->onQueue('update_menu');
        });

        $this->info('Задача по уведомлению об обновлении главного меню добавлена в очередь');

        return CommandAlias::SUCCESS;
    }
}
