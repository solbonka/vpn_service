<?php

namespace App\DTO\Actions\Subscription;


use Illuminate\Support\Carbon;

readonly class StoreSubscriptionActionDto
{
    public function __construct(
        public string $token,
        public int    $telegraphChatId,
        public int    $planId,
        public int    $durationId,
        public Carbon $endDatetime
    ) {
    }

    public function all(): array
    {
        return [
            'token'             => $this->token,
            'telegraph_chat_id' => $this->telegraphChatId,
            'plan_id'           => $this->planId,
            'duration_id'       => $this->durationId,
            'end_datetime'      => $this->endDatetime
        ];
    }
}
