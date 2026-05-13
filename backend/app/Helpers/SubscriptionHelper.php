<?php

namespace App\Helpers;

use App\Models\Duration;
use App\Models\Subscription;
use Illuminate\Support\Carbon;

class SubscriptionHelper
{
    public static function calculateNewEndDate(
        Subscription $subscription,
        Duration $duration,
        bool $enabled = true
    ): Carbon
    {
        $now = now();
        $currentEnd = $subscription->end_datetime;

        $baseDate = $currentEnd && $currentEnd->isFuture()
            ? $currentEnd
            : $now;

        return $enabled
            ? $baseDate->copy()->addDays($duration->days)
            : $currentEnd;
    }
}
