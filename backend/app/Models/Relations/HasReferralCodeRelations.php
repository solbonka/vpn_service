<?php

namespace App\Models\Relations;

use App\Models\Subscription;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasReferralCodeRelations
{
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function referredSubscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'referred_by_code_id');
    }
}
