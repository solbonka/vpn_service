<?php

namespace App\Http\Resources\Remnawave;

use Illuminate\Http\Request;
use R3bzya\Helpers\Resources\JsonResource;

class RemnawaveNodeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this['id'] ?? null,
            'name' => $this['name'] ?? null,
            'address' => $this['address'] ?? null,
            'port' => $this['port'] ?? null,
            'status' => $this['status'] ?? null,
            'users_online' => $this['users_online'] ?? 0,
            'xray_version' => $this['xray_version'] ?? null,
            'is_connected' => $this['is_connected'] ?? false,
            'is_xray_running' => $this['is_xray_running'] ?? false,
            'country_code' => $this['country_code'] ?? null,
            'created_at' => $this['created_at'] ?? null,
            'updated_at' => $this['updated_at'] ?? null,
            'usage' => [
                'download_speed_bps' => $this['usage']['downloadSpeedBps'] ?? 0,
                'upload_speed_bps' => $this['usage']['uploadSpeedBps'] ?? 0,
                'total_speed_bps' => $this['usage']['totalSpeedBps'] ?? 0,
            ]
        ];
    }
}

