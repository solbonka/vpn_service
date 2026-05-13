<?php

namespace App\Models\Relations;

use App\Models\Duration;
use App\Models\Payment;
use App\Models\PromoCodeUsage;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasPromoCodeRelations
{
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function usages(): HasMany
    {
        return $this->hasMany(PromoCodeUsage::class);
    }

    public function durations(): BelongsToMany
    {
        return $this->belongsToMany(Duration::class, 'promo_code_durations');
    }
}

