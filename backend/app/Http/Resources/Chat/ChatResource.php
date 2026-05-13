<?php

namespace App\Http\Resources\Chat;

use Illuminate\Http\Request;
use R3bzya\Helpers\Resources\JsonResource;

class ChatResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $username = $this->name ? trim(str_replace('[private]', '', $this->name)) : null;

        $tgLink = $username
            ? "https://t.me/{$username}"
            : null;

        return [
            'id' => $this->id,
            'telegram_id' => (int) $this->chat_id,
            'username' => $username,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'created_at' => $this->created_at?->toISOString(),
            'tg_link' => $tgLink,
            'is_recovery_processed' => $this->is_recovery_processed ?? false,
            'status' => $this->getStatus()
        ];
    }
}

