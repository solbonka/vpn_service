<?php

namespace App\Http\Controllers\MiniApp;

use App\Http\Controllers\Controller;
use App\Models\ClientOperatingSystem;
use App\Enums\ClientApp\ClientAppDownloadUrlEnum;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class MiniAppConnectController extends Controller
{
    public function getConnectInfo(Request $request, string $os): JsonResponse
    {
        $operatingSystem = ClientOperatingSystem::where('slug', $os)->first();

        if (!$operatingSystem) {
            Log::warning('Operating system not found:', ['os' => $os]);
            return response()->json(['error' => 'Operating system not found'], 404);
        }

        $activeApp = $operatingSystem->activeClientApps()->first();

        if (!$activeApp) {
            Log::warning('No active VPN client found for OS:', ['os' => $os]);
            return response()->json(['error' => 'No active VPN client found for this operating system'], 404);
        }

        $downloadUrls = $activeApp->getDownloadUrlsForOs($operatingSystem->id);

        $downloadLinks = [];
        if ($downloadUrls) {
            foreach ($downloadUrls as $downloadUrl) {
                $downloadLinks[] = [
                    'type' => $downloadUrl->download_url_type,
                    'url' => $downloadUrl->download_url,
                    'name' => $this->getDownloadLinkName($downloadUrl->download_url_type, $activeApp->name, $operatingSystem->name)
                ];
            }
        }

        $domain = config('telegram.domain');

        $instructions = [
            'setup' => [
                'title' => 'Установка приложения',
                'url' => $domain . '/instructions/setup/' . $os
            ],
            'connection' => [
                'title' => 'Добавление VPN ключа',
                'url' => $domain . '/instructions/connection/' . $os
            ]
        ];

        $subscription = $request->attributes->get('subscription');
        if ($subscription) {
            $subscriptionToken = $subscription->token;
            $timestamp = time();

            $relativePathKeys = route('subscription.keys', ['subscription' => $subscriptionToken], false);
            $subscriptionUrl = $domain . $relativePathKeys . "?t={$timestamp}";

            $appLink = $subscriptionUrl .
                "#" . config('app.name') . "[" . $subscription->telegraph_chat_id . "]";

            $appOpenLink = $this->generateAutoSetUrl($subscriptionToken, $timestamp, $activeApp->name);
        }

        $responseData = [
            'success' => true,
            'os' => [
                'id' => $operatingSystem->id,
                'name' => $operatingSystem->name,
                'slug' => $operatingSystem->slug
            ],
            'client_app' => [
                'id' => $activeApp->id,
                'name' => $activeApp->name,
                'display_name' => $activeApp->name
            ],
            'download_links' => $downloadLinks,
            'instructions' => $instructions,
            'auto_connect' => $appOpenLink ?? null,
            'vpn_key' => $appLink ?? null
        ];


        return response()->json($responseData);
    }

    private function getDownloadLinkName(string $type, string $appName, string $osName): string
    {
        $storeType = $this->getStoreTypeFromOs($osName);

        $region = $this->getRegionFromType($type);

        return "{$appName} в {$storeType}" . ($region ? " ({$region})" : "");
    }

    private function getStoreTypeFromOs(string $osName): string
    {
        $osName = strtolower(trim($osName));

        $osName = preg_replace('/[^\w\s]/', '', $osName);
        $osName = trim($osName);

        if (str_contains($osName, 'iphone') || str_contains($osName, 'ipad') || $osName === 'ios') {
            return 'App Store';
        }

        if (str_contains($osName, 'android')) {
            return 'Google Play';
        }

        if (str_contains($osName, 'huawei') || str_contains($osName, 'honor')) {
            return 'AppGallery';
        }

        if (str_contains($osName, 'mac')) {
            return 'App Store';
        }

        if (str_contains($osName, 'windows')) {
            return 'Официальный сайт';
        }

        return 'Официальный магазин';
    }

    private function getRegionFromType(string $type): string
    {
        return match ($type) {
            ClientAppDownloadUrlEnum::RUS->value => 'Россия',
            ClientAppDownloadUrlEnum::GLOBAL->value => 'Глобальная',
            default => ''
        };
    }

    private function generateAutoSetUrl($subscriptionToken, $timestamp, $client = 'v2RayTun'): ?string
    {
        if (!($client === 'v2RayTun' || $client === 'Happ')) {
            return null;
        }
        $relativePathConnect = route('direct.connect', [
            'client' => $client,
            'subscription' => $subscriptionToken
        ], false);

        return config('telegram.domain') . $relativePathConnect . "?t={$timestamp}";
    }


}
