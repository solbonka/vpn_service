<?php

namespace App\Enums\Subscription;

enum SubscriptionStatusEnum: string
{
    case ACTIVE = 'ACTIVE';
    case BLOCKED = 'BLOCKED';

    case EXPIRED = 'EXPIRED';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function fromMarzbanStatus(string $status): self
    {
        $status = strtolower(trim($status));

        return match ($status) {
            'active' => self::ACTIVE,
            'disabled' => self::BLOCKED,
            'expired' => self::EXPIRED,
            default => throw new \InvalidArgumentException("Неизвестный статус из Marzban: $status"),
        };
    }

    public static function fromRemnawaveStatus(string $status): self
    {
        $status = strtolower(trim($status));

        return match ($status) {
            'active' => self::ACTIVE,
            'disabled' => self::BLOCKED,
            'expired' => self::EXPIRED,
            default => throw new \InvalidArgumentException("Неизвестный статус из Remnawave: $status"),
        };
    }

    public function toMarzbanStatus(): string
    {
        return match ($this) {
            self::ACTIVE => 'active',
            self::BLOCKED => 'disabled'
        };
    }

    public function toRemnawaveStatus(): string
    {
        return match ($this) {
            self::ACTIVE => 'ACTIVE',
            self::BLOCKED => 'DISABLED',
        };
    }
}
