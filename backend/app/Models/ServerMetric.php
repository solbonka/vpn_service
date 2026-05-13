<?php

namespace App\Models;

use App\Models\Relations\HasServerMetricRelations;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperServerMetric
 */
class ServerMetric extends Model
{
    use HasServerMetricRelations;

    protected $fillable = [
        'server_id',
        'total_users',
        'active_users',
        'online_users',
    ];
}
