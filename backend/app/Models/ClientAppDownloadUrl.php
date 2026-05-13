<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperClientAppDownloadUrl
 */
class ClientAppDownloadUrl extends Model
{
    protected $fillable = [
        'client_app_operating_system_id',
        'download_url_type',
        'download_url',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeByType($query, string $type)
    {
        return $query->where('download_url_type', $type);
    }
}
