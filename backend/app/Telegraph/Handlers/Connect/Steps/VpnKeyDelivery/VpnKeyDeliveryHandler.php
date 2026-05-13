<?php

namespace App\Telegraph\Handlers\Connect\Steps\VpnKeyDelivery;

use App\Helpers\DeleteMessageHelper;
use App\Jobs\SupportMessage\SendSupportMessageJob;
use App\Models\ClientOperatingSystem;
use App\Models\Subscription;
use DefStudio\Telegraph\DTO\CallbackQuery;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Models\TelegraphChat;

class VpnKeyDeliveryHandler
{
    private TelegraphChat $chat;
    private CallbackQuery $callbackQuery;
    private string $domain;

    public function __construct(TelegraphChat $chat, CallbackQuery $callbackQuery)
    {
        $this->chat = $chat;
        $this->callbackQuery = $callbackQuery;
        $this->domain = config('telegram.domain');
    }

    public function handle(): void
    {
        DeleteMessageHelper::delete($this->chat, $this->callbackQuery);

        $data = $this->callbackQuery->data();
        $osId = $data['os_id'] ?? null;

        $operatingSystem = ClientOperatingSystem::find($osId);

        $activeApp = $operatingSystem->activeClientApps()->select('name')->first();
        $nameApp = $activeApp->name;

        $subscription = Subscription::query()->where('telegraph_chat_id', $this->chat->id)->first();

        $subscriptionToken = $subscription->token;
        $timestamp = time();

        $relativePathKeys = route('subscription.keys', ['subscription' => $subscriptionToken], false);
        $subscriptionUrl = $this->domain . $relativePathKeys . "?t={$timestamp}";

        $appLink = $subscriptionUrl .
            "#" . config('app.name') . "[" . $this->chat->id . "]";

        $keyboard = Keyboard::make();

        $appOpenLink = $this->generateAutoSetUrl($subscriptionToken, $timestamp, $nameApp);

        if ($nameApp === 'v2RayTun') {
            $message = VpnKeyDeliveryMessage::messageToV2RayTun($appLink);

            VpnKeyDeliveryButtons::buttonsToV2RayTun($keyboard, $appOpenLink, $osId);
        } elseif ($nameApp === 'Happ') {
            $message = VpnKeyDeliveryMessage::messageToHapp($appLink);

            VpnKeyDeliveryButtons::buttonsToHapp($keyboard, $appOpenLink, $osId);
        } else {
            $message = VpnKeyDeliveryMessage::messageToOtherApp($nameApp, $appLink);

            VpnKeyDeliveryButtons::buttonsToOtherApp($keyboard, $this->domain, $osId);
        }

        $this->chat->message($message)
            ->withoutPreview()
            ->markdown()
            ->keyboard($keyboard)
            ->send();

        if ($subscription->created_at->diffInSeconds(now()) < 10) {
            SendSupportMessageJob::dispatch($this->chat)
                ->delay(now()->addMinutes(5));
        }
    }

    private function generateAutoSetUrl($subscriptionToken, $timestamp, $client = 'v2RayTun'): string
    {
        $relativePathConnect = route('direct.connect', [
            'client' => $client,
            'subscription' => $subscriptionToken
        ], false);

        return $this->domain . $relativePathConnect . "?t={$timestamp}";
    }
}
