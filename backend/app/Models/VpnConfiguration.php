<?php

namespace App\Models;

use App\Enums\VpnConfiguration\VpnConfigurationTypeEnum;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperVpnConfiguration
 */
class VpnConfiguration extends Model
{
    protected $fillable = [
        'private_key',
        'public_key',
        'short_ids',
        'port',
        'base_vless_link',
        'remnawave_uuid',
    ];

    protected $casts = [
        'short_ids' => 'array'
    ];

    public static function getByType(VpnConfigurationTypeEnum $type): ?self
    {
        return self::query()->where('type', $type->value)->first();
    }
}
