<?php

namespace App\Models\Relations;


use App\Models\Plan;
use App\Models\ServerMetric;
use App\Models\VpnKey;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasServerRelations
{
    public function vpnKeys(): HasMany
    {
        return $this->hasMany(VpnKey::class);
    }

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class)->withTimestamps();
    }

    public function metrics(): HasMany
    {
        return $this->hasMany(ServerMetric::class);
    }
}
