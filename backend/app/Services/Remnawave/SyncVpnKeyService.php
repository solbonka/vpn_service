<?php

namespace App\Services\Remnawave;

use App\Enums\Subscription\SubscriptionStatusEnum;
use App\Helpers\UuidHelper;
use App\Models\Subscription;
use Illuminate\Support\Facades\Log;

class SyncVpnKeyService
{
    public function __construct(
        private readonly RemnawaveService $remnawaveService
    ) {}

    public function handle(Subscription $subscription): bool
    {
        $apiToken = config('vpn.remnawave.api_token');
        $baseUrl = config('vpn.remnawave.base_url');

        if (empty(trim($apiToken ?? '')) || empty(trim($baseUrl ?? ''))) {
            Log::info('Remnawave не настроен, пропускаем синхронизацию', [
                'subscription_id' => $subscription->id
            ]);
            return false;
        }

        $username = "user_{$subscription->telegraph_chat_id}";
        $endDateInSeconds = $subscription->getEndDatetimeSeconds();

        try {
            $response = $this->remnawaveService->getUser($username);

            if ($response) {
                $userData = json_decode($response->getBody()->getContents(), true);
                $uuid = $this->handleExistingRemnawaveUser($userData['response'], $endDateInSeconds, $subscription);
            } else {
                Log::info('Пользователь не найден в Remnawave, создаем нового', [
                    'username' => $username
                ]);
                $uuid = $this->createRemnawaveUser($username, $endDateInSeconds);
            }

            $subscription->remnawaveVpnKey()->updateOrCreate(
                ['subscription_id' => $subscription->id],
                [
                    'uuid' => $uuid,
                    'username' => $username,
                    'is_active' => $subscription->status === SubscriptionStatusEnum::ACTIVE
                ]
            );

            Log::info('Remnawave пользователь синхронизирован', [
                'subscription_id' => $subscription->id,
                'username' => $username,
                'uuid' => $uuid
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Ошибка при работе с Remnawave пользователем', [
                'subscription_id' => $subscription->id,
                'username' => $username,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    private function createRemnawaveUser(string $username, int $endDateInSeconds): string
    {
        $uuid = UuidHelper::generate();

        $data = [
            "username" => $username,
            "status" => "ACTIVE",
            "vlessUuid" => $uuid,
            "trafficLimitBytes" => 0,
            "trafficLimitStrategy" => "NO_RESET",
            'activeInternalSquads' => [env('REMNAWAVE_SQUAD_UUID', '')],
            "expireAt" => date('c', $endDateInSeconds)
        ];

        $this->remnawaveService->addUser($data);

        Log::info('Создан новый пользователь в Remnawave', [
            'username' => $username,
            'uuid' => $uuid
        ]);

        return $uuid;
    }

    private function handleExistingRemnawaveUser(array $userData, int $endDateInSeconds, Subscription $subscription): string
    {
        Log::info('Пользователь существует в Remnawave', [
            'username' => $userData['username'] ?? 'unknown'
        ]);

        $uuid = $userData['vlessUuid'];
        $remnawaveStatus = $userData['status'];
        $remnawaveExpire = $userData['expireAt'] ?? null;
        $localStatus = SubscriptionStatusEnum::fromRemnawaveStatus($remnawaveStatus);

        if ($subscription->status !== $localStatus || $endDateInSeconds !== $remnawaveExpire) {
            $this->updateRemnawaveUser($userData['username'], $uuid, $endDateInSeconds, $subscription);
        }

        return $uuid;
    }

    private function updateRemnawaveUser(string $username, string $uuid, int $endDateInSeconds, Subscription $subscription): void
    {
        $targetStatus = $subscription->status->toRemnawaveStatus();

        $data = [
            "username" => $username,
            "status" => $targetStatus
        ];

        if ($subscription->status === SubscriptionStatusEnum::ACTIVE) {
            $data["expireAt"] = date('c', $endDateInSeconds);
        }

        $this->remnawaveService->updateUser($data);

        Log::info('Обновлен пользователь в Remnawave', [
            'username' => $username,
            'uuid' => $uuid
        ]);
    }
}
