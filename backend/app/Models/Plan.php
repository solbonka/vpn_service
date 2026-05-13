<?php

namespace App\Models;

use App\Models\Relations\HasPlanRelations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperPlan
 */
class Plan extends Model
{
    use HasPlanRelations;

    protected $fillable = [
        'name',
        'price'
    ];

    public static function paidWithActiveServers(): Builder
    {
        return static::query()
            ->whereNotIn('price', [0])
            ->whereHas('servers', function ($query) {
                $query->where('is_active', true);
            });
    }
}
