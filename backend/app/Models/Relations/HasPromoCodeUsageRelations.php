<?php

namespace App\Models\Relations;

use App\Models\Payment;
use App\Models\PromoCode;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasPromoCodeUsageRelations
{
    public function promoCode(): BelongsTo
    {
        return $this->belongsTo(PromoCode::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}

