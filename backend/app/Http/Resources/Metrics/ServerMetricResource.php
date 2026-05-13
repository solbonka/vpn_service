<?php

namespace App\Http\Resources\Metrics;

use Illuminate\Http\Request;
use R3bzya\Helpers\Resources\JsonResource;

class ServerMetricResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'servers' => [
                'total'  => $this['servers_total'],
                'active' => $this['servers_active']
            ],
            'users' => [
                'total' => [
                    'count'  => $this['total_users'],
                    'growth' => $this['total_users_growth']
                ],
                'active' => [
                    'count'  => $this['active_users'],
                    'growth' => $this['active_users_growth']
                ],
                'online' => [
                    'count'  => $this['online_users'],
                    'growth' => $this['online_users_growth']
                ]
            ]
        ];
    }
}
