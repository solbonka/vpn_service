<?php

namespace App\Http\Resources\Server;

use Illuminate\Http\Request;
use R3bzya\Helpers\Resources\JsonResource;

class NodeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'name' => $this['name'] ?? null,
            'address' => $this['address'] ?? null,
            'port' => $this['port'] ?? null,
            'api_port' => $this['api_port'] ?? null,
            'usage_coefficient' => $this['usage_coefficient'] ?? null,
            'id' => $this['id'] ?? null,
            'xray_version' => $this['xray_version'] ?? null,
            'status' => $this['status'] ?? null,
            'message' => $this['message'] ?? null
        ];
    }
}
