<?php

namespace App\DTO\Actions\Subscription;


use Illuminate\Support\Carbon;

readonly class UpdateSubscriptionActionDto
{
    public function __construct(
        public int    $planId,
        public int    $durationId,
        public string $status,
        public Carbon $endDatetime
    ) {
    }

    public function all(): array
    {
        return [
            'plan_id'          => $this->planId,
            'duration_id'      => $this->durationId,
            'status'           => $this->status,
            'end_datetime'     => $this->endDatetime
        ];
    }
}
