<?php

namespace App\Models;

use App\Models\Relations\HasClientAppOperatingSystemRelations;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperClientAppOperatingSystem
 */
class ClientAppOperatingSystem extends Model
{
    use HasClientAppOperatingSystemRelations;

    protected $table = 'client_app_operating_system';

    protected $fillable = [
        'client_app_id',
        'client_operating_system_id',
        'is_active',
        'download_url' // Оставляем для обратной совместимости
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
