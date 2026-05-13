<?php

namespace App\DTO\Actions\VpnKey;


readonly class StoreVpnKeyActionDto
{
    public function __construct(
        public int    $subscriptionId,
        public int    $serverId,
        public string $username,
        public string $uuid,
        public bool   $isActive
    ) {
    }

    public function all(): array
    {
        return [
            'subscription_id' => $this->subscriptionId,
            'server_id'       => $this->serverId,
            'username'        => $this->username,
            'uuid'            => $this->uuid,
            'is_active'       => $this->isActive
        ];
    }
}
