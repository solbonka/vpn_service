<?php

namespace App\Models;

use App\Models\Relations\HasDurationRelations;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperDuration
 */
class Duration extends Model
{
    use HasDurationRelations;

    protected $fillable = [
        'name',
        'days',
        'discount_percentage',
        'is_trial'
    ];
}
