<?php

namespace App\Services\Subscription;

use App\Enums\Telegram\TelegramMessageEnum;
use App\Models\ClientApp;
use App\Models\Message;
use App\Models\Subscription;
use App\Services\Remnawave\RemnawaveService;
use App\Services\VpnKey\VpnKeyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SubscriptionMetaDataService
{
    public function generateMetaData(Subscription $subscription, ?Request $request): array
    {
        $trafficData = $this->getUserTrafficData($subscription);
        $message = Message::where('telegraph_bot_id', $subscription->telegraphChat->telegraph_bot_id)
            ->where('key', TelegramMessageEnum::ANNOUNCE->value)->first();

        $metaData = [
            'profile-title' => config('app.name') . "[" . $subscription->telegraph_chat_id . "]",
            'subscription-userinfo' => "upload={$trafficData['upload']}; download={$trafficData['download']}; total={$trafficData['total']}; expire={$trafficData['expire']};",
            'support-url' => config('telegram.support_channel_link'),
            'profile-update-interval' => 12,
            'announce' => $message?->text ?: 'Амар мэндээ в наш VPN сервис!🚀',
        ];


        if ($request && $request->userAgent()) {
            $clientApp = ClientApp::detectByUserAgent($request->userAgent());
            if ($clientApp && $clientApp->getRouting()) {
                $metaData['routing'] = $clientApp->getRouting();
            }
        }

        return $metaData;
    }

    public function getUserTrafficData(Subscription $subscription): array
    {
        if (config('vpn.provider') === 'remnawave') {
            return $this->getRemnawaveTrafficData($subscription);
        }

        return $this->getMarzbanTrafficData($subscription);
    }

    private function getRemnawaveTrafficData(Subscription $subscription): array
    {
        try {
            $remnawaveService = app(RemnawaveService::class);
            $username = "user_{$subscription->telegraph_chat_id}";

            $response = $remnawaveService->getUser($username);

            if ($response->getStatusCode() === 200) {
                $userData = json_decode($response->getBody()->getContents(), true)['response'];

                $usedTraffic = $userData['usedTrafficBytes'] ?? 0;
                $trafficLimit = $userData['trafficLimitBytes'] ?? 0;
                $expireAt = $userData['expireAt'] ?? null;

                return [
                    'upload' => $usedTraffic,
                    'download' => $usedTraffic,
                    'total' => $trafficLimit,
                    'expire' => $expireAt ? strtotime($expireAt) : 0,
                ];
            }
        } catch (\Exception $e) {
            Log::warning('Не удалось получить трафик пользователя из Remnawave', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage()
            ]);
        }

        return [
            'upload' => 0,
            'download' => 0,
            'total' => 0,
            'expire' => 0,
        ];
    }

    private function getMarzbanTrafficData(Subscription $subscription): array
    {
        $totalUpload = 0;
        $totalDownload = 0;
        $totalLimit = 0;

        $vpnKeys = $subscription->vpnKeys()->with('server')->get();

        foreach ($vpnKeys as $vpnKey) {
            try {
                $vpnKeyService = app(VpnKeyService::class);
                $username = "user_{$subscription->telegraph_chat_id}";

                $userData = $vpnKeyService->getUserFromMarzban($vpnKey->server, $username);
                if ($userData) {
                    $usedTraffic = $userData['used_traffic'] ?? 0;
                    $dataLimit = $userData['data_limit'] ?? 0;

                    $totalUpload += $usedTraffic;
                    $totalDownload += $usedTraffic;
                    $totalLimit += $dataLimit;
                }
            } catch (\Exception $e) {
                Log::warning('Не удалось получить трафик пользователя', [
                    'subscription_id' => $subscription->id,
                    'server_id' => $vpnKey->server->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return [
            'upload' => $totalUpload,
            'download' => $totalDownload,
            'total' => $totalLimit,
            'expire'=> $userData['expire'] ?? 0,
        ];
    }
}
