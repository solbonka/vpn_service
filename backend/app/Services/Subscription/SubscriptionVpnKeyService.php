<?php

namespace App\Services\Subscription;

use App\DTO\Remnawave\HostDto;
use App\Enums\VpnConfiguration\VpnConfigurationTypeEnum;
use App\Models\Subscription;
use App\Services\Remnawave\RemnawaveService;
use App\Services\SyncVpnKey\SyncVpnKeyService;
use App\Services\Remnawave\SyncVpnKeyService as RemnawaveSyncVpnKeyService;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SubscriptionVpnKeyService
{
    public function __construct(
        private readonly SubscriptionLinkService $linkService
    ) {}

    public function prepareVpnKeys(Subscription $subscription): array
    {
        if (config('vpn.provider') === 'remnawave') {
            $result = $this->prepareRemnawaveKeys($subscription);
        } else {
            $result = $this->prepareMarzbanKeys($subscription);
        }

        return $result;
    }

    private function prepareMarzbanKeys(Subscription $subscription): array
    {
        $servers = app(SyncVpnKeyService::class, ['subscription' => $subscription])->handle();
        app(RemnawaveSyncVpnKeyService::class)->handle($subscription);

        if (!$servers) {
            throw new RuntimeException('Не удалось синхронизировать VPN ключи');
        }

        $keys = $subscription->activeVpnKeys()->whereIn('server_id', $servers->pluck('id'));

        if (empty($keys)) {
            Log::error('Ключи VPN не найдены', [
                'subscription_id' => $subscription->id
            ]);

            throw new RuntimeException('Ключи VPN для этой подписки не найдены');
        }

        $vlessLinks = [];
        $firstKey = $keys->first();

        if (config('vpn.auto_key.enabled') && $firstKey) {
            $vlessLinks[] = $this->linkService->generateVlessLink(
                config('vpn.auto_key.subdomain'),
                $firstKey->uuid,
                config('vpn.auto_key.name')
            );
        }

        $subscription->fresh();

        foreach ($keys as $key) {
            $server = $key->server;

            $vlessLinks[] = $this->linkService->generateVlessLink(
                $server->subdomain_node ?? $server->subdomain,
                $key->uuid,
                $server->name,
                $server->flow == 'xtls-rprx-vision' ?
                    VpnConfigurationTypeEnum::CHINA : VpnConfigurationTypeEnum::DEFAULT
            );
        }

        $hosts = app(RemnawaveService::class)->getHosts();
        $remnawaveKey = $subscription->remnawaveVpnKey;

        if ($remnawaveKey && !empty($hosts->response)) {
            foreach ($hosts->response as $host) {
                $vlessLinks[] = $this->linkService->generateVlessLink(
                    $host->address,
                    $remnawaveKey->uuid,
                    $host->remark,
                    VpnConfigurationTypeEnum::REMNA
                );
            }
        }

        return $vlessLinks;
    }



    private function prepareRemnawaveKeys(Subscription $subscription): array
    {
        app(RemnawaveSyncVpnKeyService::class)->handle($subscription);

        $remnawaveKey = $subscription->remnawaveVpnKey;

        if (!$remnawaveKey) {
            throw new RuntimeException('Remnawave ключ не найден');
        }

        $hosts = app(RemnawaveService::class)->getHosts();

        if (empty($hosts)) {
            throw new RuntimeException('Нет доступных хостов в Remnawave');
        }

        $vlessLinks = [];

        if (config('vpn.auto_key.enabled') && $remnawaveKey) {
            $vlessLinks[] = $this->linkService->generateVlessLink(
                config('vpn.auto_key.subdomain'),
                $remnawaveKey->uuid,
                config('vpn.auto_key.name')
            );
        }

        foreach ($hosts->response as $host) {
            $vlessLinks[] = $this->linkService->generateVlessLink(
                $host->address,
                $remnawaveKey->uuid,
                $host->remark,
                VpnConfigurationTypeEnum::CHINA
            );
        }

        return $vlessLinks;
    }
}
