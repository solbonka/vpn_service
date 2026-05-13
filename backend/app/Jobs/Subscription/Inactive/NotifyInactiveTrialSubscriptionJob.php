<?php

namespace App\Jobs\Subscription\Inactive;

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

class NotifyInactiveTrialSubscriptionJob implements ShouldQueue
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
        Log::info('[NotifyInactiveTrialSubscriptionJob] START', ['subscription_ids' => $this->subscriptionIds]);

        $subscriptions = Subscription::query()
            ->whereIn('id', $this->subscriptionIds)
            ->with(['telegraphChat', 'duration'])
            ->get();

        Log::info('[NotifyInactiveTrialSubscriptionJob] Subscriptions loaded', ['count' => $subscriptions->count()]);

        $channelId = config('telegram.passive_users_channel_id');
        $botToken = env('TELEGRAM_BOT_TOKEN');

        Log::info('[NotifyInactiveTrialSubscriptionJob] Config loaded', [
            'channel_id_set' => !empty(trim($channelId ?? '')),
            'bot_token_set' => !empty(trim($botToken ?? '')),
        ]);

        if (empty(trim($channelId ?? '')) || empty(trim($botToken ?? ''))) {
            Log::warning('[NotifyInactiveTrialSubscriptionJob] Telegram channel ID or bot token not configured');
            return;
        }

        Log::info('[NotifyInactiveTrialSubscriptionJob] Starting loop', ['subscriptions_count' => $subscriptions->count()]);

        foreach ($subscriptions as $subscription) {
            Log::info('[NotifyInactiveTrialSubscriptionJob] Processing subscription', ['subscription_id' => $subscription->id]);

            $chat = $subscription->telegraphChat;

            if (!$chat) {
                Log::warning('[NotifyInactiveTrialSubscriptionJob] Chat not found', ['subscription_id' => $subscription->id]);
                continue;
            }

            Log::info('[NotifyInactiveTrialSubscriptionJob] Chat found', [
                'subscription_id' => $subscription->id,
                'chat_id' => $chat->id,
            ]);

            $notificationExists = ChatNotification::where('telegraph_chat_id', $chat->id)
                ->where('notification_type', ChatStatusEnum::BLOCKED_TRIAL->value)
                ->exists();

            if ($notificationExists) {
                Log::info('[NotifyInactiveTrialSubscriptionJob] Notification already sent, skipping', [
                    'subscription_id' => $subscription->id,
                    'chat_id' => $chat->id,
                ]);
                continue;
            }

            Log::info('[NotifyInactiveTrialSubscriptionJob] Creating message', ['subscription_id' => $subscription->id]);

            $message = NotifyInactiveTrialSubscriptionMessage::message($subscription, $chat);

            Log::info('[NotifyInactiveTrialSubscriptionJob] Message created', [
                'subscription_id' => $subscription->id,
                'message_length' => strlen($message),
            ]);

            Log::info('[NotifyInactiveTrialSubscriptionJob] Calling TelegramNotifyHelper::send', [
                'subscription_id' => $subscription->id,
                'channel_id' => $channelId,
            ]);

            $sent = TelegramNotifyHelper::send($botToken, $channelId, $message);

            Log::info('[NotifyInactiveTrialSubscriptionJob] Send result', [
                'subscription_id' => $subscription->id,
                'sent' => $sent,
            ]);

            if ($sent) {
                Log::info('[NotifyInactiveTrialSubscriptionJob] Creating ChatNotification record', [
                    'subscription_id' => $subscription->id,
                    'chat_id' => $chat->id,
                ]);

                ChatNotification::create([
                    'telegraph_chat_id' => $chat->id,
                    'notification_type' => ChatStatusEnum::BLOCKED_TRIAL->value,
                ]);

                Log::info('[NotifyInactiveTrialSubscriptionJob] ChatNotification created', [
                    'subscription_id' => $subscription->id,
                ]);
            } else {
                Log::warning('[NotifyInactiveTrialSubscriptionJob] Send returned false', [
                    'subscription_id' => $subscription->id,
                ]);
            }

            usleep(100_000);
        }

        Log::info('[NotifyInactiveTrialSubscriptionJob] END');
    }
}

