<?php

namespace App\Models;

use App\Models\Relations\HasRemnawaveVpnKeyRelations;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @mixin IdeHelperRemnawaveVpnKey
 */
class RemnawaveVpnKey extends Model
{
    use HasRemnawaveVpnKeyRelations;

    protected $fillable = [
        'subscription_id',
        'uuid',
        'username',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
