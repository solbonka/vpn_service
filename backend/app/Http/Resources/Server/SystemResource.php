<?php

namespace App\Http\Resources\Server;

use Illuminate\Http\Request;
use R3bzya\Helpers\Resources\JsonResource;

class SystemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'cpu_cores' => $this['cpu_cores'] ?? null,
            'cpu_usage' => $this['cpu_usage'] ?? null,
            'incoming_bandwidth' => $this['incoming_bandwidth'] ?? null,
            'incoming_bandwidth_speed' => $this['incoming_bandwidth_speed'] ?? null,
            'mem_total' => $this['mem_total'] ?? null,
            'mem_used' => $this['mem_used'] ?? null,
            'online_users' => $this['online_users'] ?? null,
            'outgoing_bandwidth' => $this['outgoing_bandwidth'] ?? null,
            'outgoing_bandwidth_speed' => $this['outgoing_bandwidth_speed'] ?? null,
            'total_user' => $this['total_user'] ?? null,
            'users_active' => $this['users_active'] ?? null,
            'users_disabled' => $this['users_disabled'] ?? null,
            'users_expired' => $this['users_expired'] ?? null,
            'users_limited' => $this['users_limited'] ?? null,
            'users_on_hold' => $this['users_on_hold'] ?? null,
            'version' => $this['version'] ?? null
        ];
    }
}
