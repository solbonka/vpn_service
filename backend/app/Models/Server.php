<?php

namespace App\Models;

use App\Models\Relations\HasServerRelations;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperServer
 */
class Server extends Model
{
    use HasServerRelations;

    protected $fillable = [
        'base_url',
        'login',
        'password',
        'host',
        'name',
        'code',
        'subdomain',
        'subdomain_node',
        'is_active',
        'order',
        'flow',
        'remnawave_uuid'
    ];
}
