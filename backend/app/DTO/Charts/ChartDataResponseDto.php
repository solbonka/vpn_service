<?php

namespace App\DTO\Charts;

readonly class ChartDataResponseDto
{
    public function __construct(
        public array  $data,
        public string $period,
        public bool   $success = true
    ) {}

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'data' => $this->data,
            'period' => $this->period
        ];
    }
}
