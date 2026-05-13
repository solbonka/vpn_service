<?php

namespace App\DTO\Analytics;

readonly class PeriodStatsDto
{
    public function __construct(
        public string  $period,
        public int     $newReferrals,
        public int     $paymentsCount,
        public float   $revenue,
        public int     $ticketsCount,
        public ?string $sourceType = null,
    ) {}

    public function toArray(): array
    {
        $data = [
            'period' => $this->period,
            'new_referrals' => $this->newReferrals,
            'payments_count' => $this->paymentsCount,
            'revenue' => $this->revenue,
            'tickets_count' => $this->ticketsCount,
        ];

        if ($this->sourceType !== null) {
            $data['source_type'] = $this->sourceType;
        }

        return $data;
    }
}
