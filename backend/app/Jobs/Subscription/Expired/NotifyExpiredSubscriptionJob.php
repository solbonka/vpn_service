<?php

namespace App\Jobs\Subscription\Expired;

use App\Enums\Subscription\SubscriptionStatusEnum;
use App\Helpers\ExtensionButtonHelper;
use App\Models\Subscription;
use DefStudio\Telegraph\Models\TelegraphChat;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class NotifyExpiredSubscriptionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1800;
    public int $tries = 3;

    public array $subscriptionBlockedIds;
    private bool $isPayment;

    public function __construct(array $subscriptionBlockedIds)
    {
        $this->subscriptionBlockedIds = $subscriptionBlockedIds;
        $this->isPayment = config('payment.enabled');
    }

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        $blockedSubscriptions = Subscription::query()
            ->whereIn('id', $this->subscriptionBlockedIds)
            ->where('status', SubscriptionStatusEnum::BLOCKED)
            ->get();

        $chatIds = $blockedSubscriptions->pluck('telegraph_chat_id')->unique();
        $chats = TelegraphChat::query()->whereIn('id', $chatIds)->get()->keyBy('id');

        $now = Carbon::now();

        foreach ($blockedSubscriptions as $subscription) {
            $chat = $chats->get($subscription->telegraph_chat_id);

            $hoursSinceBlocked = Carbon::parse($subscription->end_datetime)->diffInHours($now);
            $daysSinceBlocked = Carbon::parse($subscription->end_datetime)->diffInDays($now);


            $shouldNotify = $this->shouldSendNotification($hoursSinceBlocked, $daysSinceBlocked);

            if ($shouldNotify) {
                Log::info('Лог перед отправкой сообщения', [
                    'subscription_id' => $subscription->id,
                    'chat_id' => $chat->id
                ]);

                if ($this->isPayment) {
                    $chat->message(NotifyExpiredSubscriptionMessage::messagePayment())
                        ->keyboard(ExtensionButtonHelper::buttons(
                            $subscription->plan_id,
                            $subscription->duration_id
                        ))
                        ->markdown()
                        ->send();
                } else {
                    $chat->message(NotifyExpiredSubscriptionMessage::messageNotPayment())
                        ->markdown()
                        ->send();
                }

                Log::info('Предложено продление подписки пользователю, т.к. она истекла', [
                    'subscription_id' => $subscription->id,
                    'chat_id' => $chat->id
                ]);
            }

            usleep(100_000);
        }
    }

    private function shouldSendNotification(int $hoursSinceBlocked, int $daysSinceBlocked): bool
    {
        if ($daysSinceBlocked === 0 || ($daysSinceBlocked === 1 && $hoursSinceBlocked === 24)) {
            return in_array($hoursSinceBlocked, [6, 12, 24]);
        }

        return $hoursSinceBlocked % 24 === 0 && $hoursSinceBlocked >= 48;
    }
}
