<?php

namespace App\Services\Subscription;

use App\Enums\VpnConfiguration\VpnConfigurationTypeEnum;
use App\Models\ClientApp;
use App\Models\VpnConfiguration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SubscriptionLinkService
{
    private readonly string $domain;

    public function __construct()
    {
        $this->domain = config('telegram.domain');
    }

    public function generateAppLink(string $token, int $chatId, ClientApp $client): string
    {
        $timestamp = time();

        $relativePath = route('subscription.keys', ['subscription' => $token], false);
        $subscriptionUrl = $this->domain . $relativePath . "?t={$timestamp}";

        if ($client->name == 'Happ') {
            $appLink = $this->generateHappLink($subscriptionUrl);
        } else {
            $appLink = "v2raytun://import/" .
                rawurlencode($subscriptionUrl) .
                "#" . config('app.name') . "[" . $chatId . "]";
        }

        Log::info('Сгенерирована ссылка для прямого открытия приложения', [
            'app_link' => $appLink
        ]);

        return $appLink;
    }

    public function generateHappLink(string $url): ?string
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post('https://crypto.happ.su/api.php', [
            'url' => $url
        ]);

        if ($response->successful()) {
            return $response->json('encrypted_link');
        }

        return '';
    }

    public function generateVlessLink(
        string $subdomain,
        string $uuid,
        string $name,
        VpnConfigurationTypeEnum $type = VpnConfigurationTypeEnum::DEFAULT
    ): string
    {
        $vpnConfig = VpnConfiguration::getByType($type);

        $baseUrl = "vless://$uuid@$subdomain:$vpnConfig->port";

        parse_str($vpnConfig->base_vless_link, $baseParams);

        $params = array_merge($baseParams, [
            'pbk' => $vpnConfig->public_key,
            'sid' => $vpnConfig->short_ids[0],
        ]);

         if (!empty($server->flow)) {
             $params['flow'] = $server->flow;
         }

        $queryString = http_build_query($params);

        $fullRemark = rawurlencode("{$name}");

        return "{$baseUrl}?{$queryString}#{$fullRemark}";
    }
}
