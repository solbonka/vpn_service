<?php

namespace App\Jobs\Subscription\Expiring;

use App\Helpers\ExtensionButtonHelper;
use App\Models\Subscription;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotifyExpiringSubscriptionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1800;
    public int $tries = 3;
    private bool $isPayment;

    public array $subscriptionExpiringIds;

    public function __construct(array $subscriptionExpiringIds)
    {
        $this->isPayment = config('payment.enabled');
        $this->subscriptionExpiringIds = $subscriptionExpiringIds;
    }

    public function handle(): void
    {
        $expiringSubscriptions = Subscription::query()->whereIn('id', $this->subscriptionExpiringIds)->get();

        $chatIds = $expiringSubscriptions->pluck('telegraph_chat_id')->unique();
        $chats = TelegraphChat::whereIn('id', $chatIds)->get()->keyBy('id');

        foreach ($expiringSubscriptions as $subscription) {
            $chat = $chats->get($subscription->telegraph_chat_id);

            if ($this->isPayment) {
                $chat->message(NotifyExpiringSubscriptionMessage::messagePayment())
                    ->keyboard(ExtensionButtonHelper::buttons(
                        $subscription->plan_id,
                        $subscription->duration_id
                    ))
                    ->markdown()
                    ->send();
            } else {
                $chat->message(NotifyExpiringSubscriptionMessage::messageNotPayment())
                    ->markdown()
                    ->send();
            }

            Log::info('Предложено продление подписки пользователю', [
                'subscription_id' => $subscription->id,
                'chat_id' => $chat->id
            ]);

            usleep(100_000);
        }
    }
}
