<?php

namespace App\Telegraph\Handlers\Extension;

use App\Enums\Subscription\SubscriptionStatusEnum;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\Subscription\SubscriptionService;
use App\Services\SyncVpnKey\SyncVpnKeyService;
use App\Services\Remnawave\SyncVpnKeyService as RemnawaveSyncVpnKeyService;
use App\Telegraph\Handlers\BaseMessageHandler;
use App\Telegraph\Handlers\Extension\Steps\Duration\DurationHandler;
use App\Telegraph\Handlers\Extension\Steps\Tariff\TariffHandler;
use Exception;
use Illuminate\Support\Facades\Log;

class ExtensionButtonHandler extends BaseMessageHandler
{
    private bool $isPayment;

    protected function initialize(): void
    {
        $this->isPayment = config('payment.enabled');
    }

    public function canHandle(string $message): bool
    {
        return $message === '🔄️ Продлить подписку';
    }

    /**
     * @throws Exception
     */
    public function handle(string $message): void
    {
        if ($this->isPayment) {
            $this->handlePaymentFlow();
        } else {
            $this->handleSubscriptionActivation();
        }
    }

    /**
     * @throws Exception
     */
    private function handlePaymentFlow(): void
    {
        $plansCount = Plan::paidWithActiveServers()->count();

        if ($plansCount > 1) {
            app(TariffHandler::class, ['chat' => $this->chat])->handle();
        } else {
            app(DurationHandler::class, ['chat' => $this->chat])->handle();
        }
    }

    /**
     * @throws Exception
     */
    private function handleSubscriptionActivation(): void
    {
        $subscriptionBlocked = Subscription::query()
            ->where('telegraph_chat_id', $this->chat->id)
            ->where('status', SubscriptionStatusEnum::BLOCKED)
            ->first();


        if (! $subscriptionBlocked) {
            $message = "
Ваша подписка еще АКТИВНА, продление будет доступно после
истечения срока подписки!
";
            $this->chat->message($message)->send();

            return;
        }

        $subscriptionUpdated = app(SubscriptionService::class, ['chat' => $this->chat])
            ->update($subscriptionBlocked);

        if (! $subscriptionUpdated) {
            Log::error("Не удалось активировать подписку для чата: {$this->chat->id}");
            return;
        }

        $servers = app(SyncVpnKeyService::class, ['subscription' => $subscriptionUpdated])->handle();
        app(RemnawaveSyncVpnKeyService::class)->handle($subscriptionUpdated);

        if (!$servers) {
            Log::error("Не удалось активировать VPN ключи для чата: {$this->chat->id}");
            return;
        }

        Log::info("Marzban VPN ключи активированы для чата: {$this->chat->id}");
        Log::info("Remnawave VPN ключи активированы для чата: {$this->chat->id}");

        $this->chat->message('Ваша подписка активирована.')->send();
    }
}
