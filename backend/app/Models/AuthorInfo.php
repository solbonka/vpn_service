<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperAuthorInfo
 */
class AuthorInfo extends Model
{
    protected $fillable = [
        'text'
    ];
}
