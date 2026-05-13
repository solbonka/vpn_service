<?php

namespace App\Jobs\Subscription\Passive;

use App\Enums\Chat\ChatStatusEnum;
use App\Helpers\TelegramNotifyHelper;
use App\Models\ChatNotification;
use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotifyPassiveSubscriptionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1800;
    public int $tries = 3;

    public array $subscriptionIds;

    public function __construct(array $subscriptionIds)
    {
        $this->subscriptionIds = $subscriptionIds;
    }

    public function handle(): void
    {
        $subscriptions = Subscription::query()
            ->whereIn('id', $this->subscriptionIds)
            ->with('telegraphChat')
            ->get();

        $channelId = config('telegram.passive_users_channel_id');
        $botToken = env('TELEGRAM_BOT_TOKEN');

        if (empty(trim($channelId ?? '')) || empty(trim($botToken ?? ''))) {
            Log::warning('Telegram channel ID or bot token not configured for passive users notifications');
            return;
        }

        foreach ($subscriptions as $subscription) {
            $chat = $subscription->telegraphChat;

            if (!$chat) {
                continue;
            }

            if (ChatNotification::where('telegraph_chat_id', $chat->id)
                ->where('notification_type', ChatStatusEnum::PASSIVE->value)
                ->exists()) {
                continue;
            }

            $message = NotifyPassiveSubscriptionMessage::message($chat);
            $sent = TelegramNotifyHelper::send($botToken, $channelId, $message);

            if ($sent) {
                ChatNotification::create([
                    'telegraph_chat_id' => $chat->id,
                    'notification_type' => ChatStatusEnum::PASSIVE->value,
                ]);
            }

            usleep(100_000);
        }
    }
}

