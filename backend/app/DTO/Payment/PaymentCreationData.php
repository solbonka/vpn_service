<?php

namespace App\DTO\Payment;

use App\Models\Duration;
use App\Models\Plan;

class PaymentCreationData
{
    public function __construct(
        public Plan $plan,
        public Duration $duration,
        public int $price,
        public int $chatId
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            plan: Plan::query()->find($data['plan_id']),
            duration: Duration::query()->find($data['duration_id']),
            price: $data['price'],
            chatId: $data['chat_id']
        );
    }
}

