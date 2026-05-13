<?php

namespace App\Http\Resources\Metrics;

use Illuminate\Http\Request;
use R3bzya\Helpers\Resources\JsonResource;

class PaymentMetricResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'total_income' => [
                'count'  => $this['total_income'],
                'growth' => $this['total_income_growth']
            ],
            'monthly_income' => [
                'count'  => $this['monthly_income'],
                'growth' => $this['monthly_income_growth']
            ],
            'average_check' => [
                'count'  => $this['average_check'],
                'growth' => $this['average_check_growth']
            ],
            'monthly_average_check' => [
                'count'  => $this['monthly_average_check'],
                'growth' => $this['monthly_average_check_growth']
            ]
        ];
    }
}
