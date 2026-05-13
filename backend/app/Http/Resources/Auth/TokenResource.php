<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use R3bzya\Helpers\Resources\JsonResource;

class TokenResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'access_token' => $this['access_token'],
            'expires_in' => $this['expires_in']
        ];
    }
}
