<?php

namespace App\Models\Relations;

use App\Models\ChatNotification;
use App\Models\ClientOperatingSystem;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasCustomTelegraphChatRelations
{
    public function clientOperatingSystem(): BelongsTo
    {
        return $this->belongsTo(ClientOperatingSystem::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'telegraph_chat_id');
    }

    public function chatNotifications(): HasMany
    {
        return $this->hasMany(ChatNotification::class, 'telegraph_chat_id');
    }
}
