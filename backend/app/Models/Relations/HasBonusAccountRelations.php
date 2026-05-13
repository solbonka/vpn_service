<?php

namespace App\Models\Relations;

use App\Models\Subscription;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasBonusAccountRelations
{
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
