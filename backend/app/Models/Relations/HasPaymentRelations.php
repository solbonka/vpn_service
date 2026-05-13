<?php

namespace App\Models\Relations;


use App\Models\PromoCode;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasPaymentRelations
{
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function promoCode(): BelongsTo
    {
        return $this->belongsTo(PromoCode::class);
    }
}
