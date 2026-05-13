<?php

namespace App\Http\Resources\Metrics;

use Illuminate\Http\Request;
use R3bzya\Helpers\Resources\JsonResource;

class SubscriptionMetricResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'active' => [
                'count'  => $this['sub_active_total'],
                'growth' => $this['sub_active_growth']
            ],
            'blocked' => [
                'count'  => $this['sub_blocked_total'],
                'growth' => $this['sub_blocked_growth']
            ],
            'new' => [
                'count'  => $this['sub_new_total'],
                'growth' => $this['sub_new_growth']
            ],
        ];
    }
}
