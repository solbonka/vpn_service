<?php

namespace App\Models;

use App\Models\Relations\HasVpnKeyRelations;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperVpnKey
 */
class VpnKey extends Model
{
    use HasVpnKeyRelations;

    protected $fillable = [
        'subscription_id',
        'server_id',
        'username',
        'uuid',
        'is_active'
    ];
}
