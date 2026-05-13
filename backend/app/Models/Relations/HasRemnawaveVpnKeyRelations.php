<?php

namespace App\Models\Relations;

use App\Models\Subscription;

trait HasRemnawaveVpnKeyRelations
{
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}
