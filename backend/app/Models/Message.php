<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperMessage
 */
class Message extends Model
{
    protected $fillable = [
        'text',
        'key',
        'telegraph_bot_id',
    ];
}
