<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Duration;
use App\Models\Plan;

class WebSubscriptionController extends Controller
{
    public function getPlans()
    {
        $plans = Plan::paidWithActiveServers()->with('servers')->get();
        $durations = Duration::query()->where('is_trial', false)->get();

        return response()->json([
            'success' => true,
            'plans' => $plans->map(fn (Plan $plan) => [
                'id' => $plan->id,
                'name' => $plan->name,
                'price' => $plan->price,
                'servers_count' => $plan->servers->count(),
            ]),
            'durations' => $durations->map(fn (Duration $duration) => [
                'id' => $duration->id,
                'name' => $duration->name,
                'days' => $duration->days,
                'discount_percentage' => $duration->discount_percentage,
            ]),
        ]);
    }
}
