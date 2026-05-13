<?php

namespace App\Models\Relations;


use App\Models\Plan;
use App\Models\Server;
use App\Models\VpnKey;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasServerMetricRelations
{
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }
}
