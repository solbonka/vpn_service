<?php

namespace App\Jobs\Subscription\Blocked;

use App\Enums\Subscription\SubscriptionStatusEnum;
use App\Helpers\ExtensionButtonHelper;
use App\Models\Subscription;
use App\Services\Subscription\SubscriptionService;
use App\Services\SyncVpnKey\SyncVpnKeyService;
use App\Services\Remnawave\SyncVpnKeyService as RemnawaveSyncVpnKeyService;
use DefStudio\Telegraph\Models\TelegraphChat;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BlockedSubscriptionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1800;
    public int $tries = 3;

    public array $subscriptionExpiredIds;
    private bool $isPayment;

    public function __construct(array $subscriptionExpiredIds)
    {
        $this->subscriptionExpiredIds = $subscriptionExpiredIds;
        $this->isPayment = config('payment.enabled');
    }

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        $expiredSubscriptions = Subscription::query()
            ->whereIn('id', $this->subscriptionExpiredIds)
            ->where('status', SubscriptionStatusEnum::ACTIVE)
            ->get();

        if ($expiredSubscriptions->isEmpty()) {
            Log::info('Подписки уже заблокированы.');
            return;
        }

        $chatIds = $expiredSubscriptions->pluck('telegraph_chat_id')->unique();
        $chats = TelegraphChat::query()->whereIn('id', $chatIds)->get()->keyBy('id');

        foreach ($expiredSubscriptions as $subscription) {
            $chat = $chats->get($subscription->telegraph_chat_id);

            $this->blockedSubscription($subscription, $chat);

            $this->blockedVpnKeys($subscription, $chat);

            if ($this->isPayment) {
                $chat->message(BlockedSubscriptionMessage::messagePayment())
                    ->keyboard(ExtensionButtonHelper::buttons(
                        $subscription->plan_id,
                        $subscription->duration_id
                    ))
                    ->markdown()
                    ->send();
            } else {
                $chat->message(BlockedSubscriptionMessage::messageNotPayment())
                    ->markdown()
                    ->send();
            }


            usleep(100_000);
        }
    }

    /**
     * @throws Exception
     */
    private function blockedSubscription(Subscription $subscription, TelegraphChat $chat): void
    {
        $subscriptionUpdated = app(SubscriptionService::class, [
            'chat' => $chat
        ])->update(
            $subscription,
            $subscription->plan_id,
            $subscription->duration_id,
            false
        );


        if (!$subscriptionUpdated) {
            Log::error("Не удалось заблокировать подписку для чата: $chat->id");
            throw new Exception("Ошибка при блокировке подписки");
        }

        Log::info("Подписка заблокирована для чата: $chat->id");
    }

    /**
     * @throws Exception
     */
    private function blockedVpnKeys(Subscription $subscription, TelegraphChat $chat): void
    {
        if ($subscription->hasActiveVpnKey()) {
            $servers = app(SyncVpnKeyService::class, ['subscription' => $subscription])->handle();
            app(RemnawaveSyncVpnKeyService::class)->handle($subscription);

            if (!$servers) {
                Log::error("Не удалось заблокировать VPN ключи для чата: {$chat->id}");
                throw new Exception("Ошибка при блокировке VPN ключей");
            }

            Log::info("Marzban VPN ключи активированы для чата: {$subscription->telegraph_chat_id}");
            Log::info("Remnawave VPN ключи активированы для чата: {$subscription->telegraph_chat_id}");
        }
    }
}
