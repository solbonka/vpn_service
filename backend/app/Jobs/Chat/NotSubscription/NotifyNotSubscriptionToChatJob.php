<?php

namespace App\Jobs\Chat\NotSubscription;

use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotifyNotSubscriptionToChatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1800;
    public int $tries = 3;

    public array $chatIds;

    public function __construct(array $chatIds)
    {
        $this->chatIds = $chatIds;
    }

    public function handle(): void
    {
        $chats = TelegraphChat::query()->whereIn('id', $this->chatIds)->get();

        foreach ($chats as $chat) {
            $chat->message(NotifyNotSubscriptionToChatMessage::message())
                ->markdown()
                ->keyboard(NotifyNotSubscriptionToChatButtons::buttons($chat))
                ->send();

            usleep(100_000);
        }

        Log::info("Уведомления для всех чатов отправлены");
    }
}
