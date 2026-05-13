<?php

namespace App\DTO\Actions\VpnKey;


readonly class UpdateVpnKeyActionDto
{
    public function __construct(
        public bool $isActive,
        public string $uuid
    ) {
    }

    public function all(): array
    {
        return [
            'is_active' => $this->isActive,
            'uuid' => $this->uuid
        ];
    }
}
