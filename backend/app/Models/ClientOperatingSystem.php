<?php

namespace App\Models;

use App\Models\Relations\HasClientOperatingSystemRelations;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperClientOperatingSystem
 */
class ClientOperatingSystem extends Model
{
    use HasClientOperatingSystemRelations;

    protected $fillable = [
        'name'
    ];
}
