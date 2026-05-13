<?php

namespace App\Helpers;

class PricingHelper
{
    public static function calculateDiscountedPrice(float $basePrice, int $days, float $discountPercent): array
    {
        $months = floor($days / 30);
        $oldPrice = $basePrice * $months;
        $discountedPrice = round($oldPrice * (1 - $discountPercent / 100), 2);

        return [
            'oldPrice'        => $oldPrice,
            'discountedPrice' => $discountedPrice,
            'discountPercent' => $discountPercent,
        ];
    }
}
