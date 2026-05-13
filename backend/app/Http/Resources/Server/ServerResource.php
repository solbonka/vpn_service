<?php

namespace App\Http\Resources\Server;

use Illuminate\Http\Request;
use R3bzya\Helpers\Resources\JsonResource;

class ServerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this['id'] ?? null,
            'name' => $this['name'] ?? null,
            'code' => $this['code'] ?? null,
            'is_active' => $this['is_active'] ?? null,
            'system' => isset($this['system']) ? new SystemResource($this['system']) : null,
            'nodes' => isset($this['nodes']) ? NodeResource::collection($this['nodes']) : null
        ];
    }
}
