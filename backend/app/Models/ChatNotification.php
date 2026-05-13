<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperChatNotification
 */
class ChatNotification extends Model
{
    protected $table = 'chat_notifications';

    protected $fillable = [
        'telegraph_chat_id',
        'notification_type',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function telegraphChat(): BelongsTo
    {
        return $this->belongsTo(CustomTelegraphChat::class);
    }
}

