<?php

namespace App\DTO\Analytics;

readonly class ConversionStatsDto
{
    public function __construct(
        public string $period,
        public int    $newReferrals,
        public int    $convertedReferrals,
        public float  $conversionRate,
        public float  $revenue,
    ) {}

    public function toArray(): array
    {
        return [
            'period' => $this->period,
            'month' => $this->period, // для совместимости с фронтендом
            'new_referrals' => $this->newReferrals,
            'converted_referrals' => $this->convertedReferrals,
            'conversion_rate' => $this->conversionRate,
            'revenue' => $this->revenue,
        ];
    }
}
