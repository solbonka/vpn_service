<?php

namespace App\Enums\Payment;

enum PaymentStatusEnum: string
{
    case PENDING = 'PENDING';
    case SUCCEEDED = 'SUCCEEDED';
    case CANCELED = 'CANCELED';
    case FAILED = 'FAILED';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
