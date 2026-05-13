<?php

namespace App\Models\Relations;


use App\Models\Server;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasVpnKeyRelations
{
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
