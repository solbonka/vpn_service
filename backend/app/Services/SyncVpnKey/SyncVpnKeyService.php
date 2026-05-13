<?php

namespace App\Services\SyncVpnKey;

use App\Actions\VpnKey\StoreVpnKeyAction;
use App\Actions\VpnKey\UpdateVpnKeyAction;
use App\DTO\Actions\VpnKey\StoreVpnKeyActionDto;
use App\DTO\Actions\VpnKey\UpdateVpnKeyActionDto;
use App\Enums\Subscription\SubscriptionStatusEnum;
use App\Helpers\UuidHelper;
use App\Models\Server;
use App\Models\Subscription;
use App\Models\VpnKey;
use App\Services\Telegram\ErrorNotificationService;
use App\Services\VpnKey\VpnKeyService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncVpnKeyService
{
    private Subscription $subscription;
    private VpnKeyService $vpnKeyService;
    private ErrorNotificationService $errorNotificationService;
    private string $marzbanUsername;
    private string $generatedUuid;

    public function __construct(Subscription $subscription, VpnKeyService $vpnKeyService, ErrorNotificationService $errorNotificationService)
    {
        $this->subscription = $subscription;
        $this->vpnKeyService = $vpnKeyService;
        $this->errorNotificationService = $errorNotificationService;
        $this->marzbanUsername = "user_$subscription->telegraph_chat_id";
        $this->generatedUuid = UuidHelper::generate();
    }

    public function handle(): Collection|false
    {
        $activeServers = $this->syncWithServers($this->subscription->activeServers());

        if ($activeServers->isEmpty()) {
            Log::info('Нет серверов для обработки', [
                'subscription_id' => $this->subscription->id,
                'username' => $this->marzbanUsername
            ]);

            return false;
        }

        return $activeServers;
    }

    private function syncWithServers(Collection $servers): Collection
    {
        $subscriptionEndDateInSeconds = $this->subscription->getEndDatetimeSeconds();
        $successfulServers = collect();

        foreach ($servers as $server) {
            try {
                $serverSyncResult = $this->syncWithSingleServer(
                    $server,
                    $subscriptionEndDateInSeconds
                );

                if ($serverSyncResult) {
                    $successfulServers->push($server);
                }
            } catch (Throwable $e) {
                Log::error("Ошибка для: {$this->marzbanUsername} на сервере: {$server->id}: {$e->getMessage()}", [
                    'server_id' => $server->id,
                    'username' => $this->marzbanUsername,
                    'exception' => $e
                ]);

                $this->errorNotificationService->sendErrorNotification($e, [
                    'service' => 'SyncVpnKeyService',
                    'method' => 'syncWithServers',
                    'server_id' => $server->id,
                    'username' => $this->marzbanUsername,
                    'subscription_id' => $this->subscription->id,
                    'chat_id' => $this->subscription->telegraph_chat_id,
                ]);
            }
        }

        return $successfulServers;
    }

    /**
     * @throws GuzzleException
     */
    private function syncWithSingleServer(Server $server, int $endDateInSeconds): bool
    {
        $marzbanUserData = $this->vpnKeyService->getUserFromMarzban(
            $server,
            $this->marzbanUsername
        );

        $actualUuid = $this->generatedUuid;

        if ($marzbanUserData) {
            $actualUuid = $this->handleExistingMarzbanUser(
                $server,
                $marzbanUserData,
                $endDateInSeconds
            );
        } else {
            $this->createNewMarzbanUser($server, $endDateInSeconds);
        }

        $this->syncVpnKeyInDatabase($server, $actualUuid);

        return true;
    }

    /**
     * @throws GuzzleException
     */
    private function handleExistingMarzbanUser(
        Server $server,
        array $marzbanUserData,
        int $endDateInSeconds
    ): string
    {
        Log::info("Пользователь существует на сервере: {$server->id}", [
            'username' => $this->marzbanUsername
        ]);

        $userUuid = $marzbanUserData['proxies']['vless']['id'];
        $marzbanStatus = $marzbanUserData['status'];
        $mazbanExpire = $marzbanUserData['expire'];
        $localStatus = SubscriptionStatusEnum::fromMarzbanStatus($marzbanStatus);


        if ($this->subscription->status !== $localStatus || $endDateInSeconds !== $mazbanExpire) {
            $this->updateMarzbanUserStatus($server, $userUuid, $endDateInSeconds);
        }

        return $userUuid;
    }

    /**
     * @throws GuzzleException
     */
    private function updateMarzbanUserStatus(Server $server, string $uuid, int $endDateInSeconds): void
    {
        $targetMarzbanStatus = $this->subscription->status->toMarzbanStatus();

        $this->vpnKeyService->updateUserFromMarzban(
            $server,
            $uuid,
            $this->marzbanUsername,
            $endDateInSeconds,
            $targetMarzbanStatus
        );

        Log::info("Пользователь обновлен на сервере Marzban: {$server->id}", [
            'username' => $this->marzbanUsername
        ]);
    }

    /**
     * @throws GuzzleException
     */
    private function createNewMarzbanUser(Server $server, int $endDateInSeconds): void
    {
        $this->vpnKeyService->createUserToMarzban(
            $server,
            $this->generatedUuid,
            $this->marzbanUsername,
            $endDateInSeconds
        );

        Log::info("Пользователь создан на сервере: {$server->id}", [
            'username' => $this->marzbanUsername,
            'uuid' => $this->generatedUuid
        ]);    }

    private function syncVpnKeyInDatabase(Server $server, string $actualUuid): void
    {
        $isKeyActive = $this->subscription->status === SubscriptionStatusEnum::ACTIVE;

        $existingVpnKey = $this->findExistingVpnKey($server);

        if (!$existingVpnKey) {
            $this->createNewVpnKey($server, $actualUuid, $isKeyActive);
            return;
        }

        if ($this->shouldUpdateVpnKey($existingVpnKey, $actualUuid, $isKeyActive)) {
            $this->updateExistingVpnKey($existingVpnKey, $actualUuid, $isKeyActive);
        }
    }

    private function findExistingVpnKey(Server $server): ?VpnKey
    {
        return $this->subscription
            ->vpnKeys()
            ->where('server_id', $server->id)
            ->first();
    }

    private function shouldUpdateVpnKey(VpnKey $vpnKey, string $uuid, bool $isActive): bool
    {
        return $vpnKey->is_active !== $isActive || $vpnKey->uuid !== $uuid;
    }

    private function createNewVpnKey(Server $server, string $uuid, bool $isActive): void
    {
        $newVpnKey = app(StoreVpnKeyAction::class)->execute(new StoreVpnKeyActionDto(
            subscriptionId: $this->subscription->id,
            serverId: $server->id,
            username: $this->marzbanUsername,
            uuid: $uuid,
            isActive: $isActive
        ));

        Log::info("Ключ с id: {$newVpnKey->id} для сервера: {$server->id} создан", [
            'subscription_id' => $this->subscription->id,
            'uuid' => $newVpnKey,
            'username' => $this->marzbanUsername,
        ]);
    }

    private function updateExistingVpnKey(VpnKey $vpnKey, string $uuid, bool $isActive): void
    {
        $updatedVpnKey = app(UpdateVpnKeyAction::class)->execute(
            new UpdateVpnKeyActionDto(
                isActive: $isActive,
                uuid: $uuid
            ),
            $vpnKey
        );

        Log::info("Ключ с id: {$updatedVpnKey->id} для сервера обновлен", [
            'subscription_id' => $this->subscription->id,
            'old_uuid' => $vpnKey->uuid,
            'new_uuid' => $updatedVpnKey->uuid,
            'username' => $this->marzbanUsername,
        ]);
    }
}
