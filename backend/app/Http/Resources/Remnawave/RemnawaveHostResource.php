<?php

namespace App\Http\Resources\Remnawave;

use Illuminate\Http\Request;
use R3bzya\Helpers\Resources\JsonResource;

class RemnawaveHostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this['id'] ?? null,
            'name' => $this['name'] ?? null,
            'address' => $this['address'] ?? null,
            'status' => $this['status'] ?? null,
            'security_layer' => $this['security_layer'] ?? null,
            'total_users' => $this['total_users'] ?? 0,
            'online_users' => $this['online_users'] ?? 0,
            'nodes_count' => $this['nodes_count'] ?? 0,
            'view_position' => $this['view_position'] ?? 0,
            'is_hidden' => $this['is_hidden'] ?? false,
            'nodes' => isset($this['nodes']) ? RemnawaveNodeResource::collection($this['nodes']) : []
        ];
    }
}

