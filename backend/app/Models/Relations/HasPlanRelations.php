<?php

namespace App\Models\Relations;


use App\Models\Server;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasPlanRelations
{
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function servers(): BelongsToMany
    {
        return $this->belongsToMany(Server::class)->withTimestamps();
    }
}
