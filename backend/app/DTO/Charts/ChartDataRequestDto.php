<?php

namespace App\DTO\Charts;

use Illuminate\Support\Carbon;

readonly class ChartDataRequestDto
{
    public function __construct(
        public string  $period,
        public ?string $startDate = null,
        public ?string $endDate = null
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            period: $data['period'] ?? '7d',
            startDate: $data['start_date'] ?? null,
            endDate: $data['end_date'] ?? null
        );
    }

    public function isCustomDateRange(): bool
    {
        return $this->startDate !== null && $this->endDate !== null;
    }

    public function getDaysCount(): int
    {
        if ($this->isCustomDateRange()) {
            return Carbon::parse($this->startDate)->diffInDays(Carbon::parse($this->endDate)) + 1;
        }

        return match($this->period) {
            '7d' => 7,
            '30d' => 30,
            '90d' => 90,
            default => 7
        };
    }
}
