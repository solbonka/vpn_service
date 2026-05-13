<?php

namespace App\Models;

use App\Models\Relations\HasPromoCodeUsageRelations;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperPromoCodeUsage
 */
class PromoCodeUsage extends Model
{
    use HasPromoCodeUsageRelations;

    protected $fillable = [
        'promo_code_id',
        'subscription_id',
        'payment_id',
        'original_amount',
        'discount_amount',
        'final_amount',
    ];

    protected $casts = [
        'original_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
    ];
}

