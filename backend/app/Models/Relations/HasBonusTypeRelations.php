<?php

namespace App\Models\Relations;

use App\Models\ReferralCode;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasBonusTypeRelations
{
    public function referralCodes(): HasMany
    {
        return $this->hasMany(ReferralCode::class);
    }
}
