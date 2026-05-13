<?php

namespace App\Models\Relations;

use App\Models\Subscription;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasLotteryTicketRelations
{
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
