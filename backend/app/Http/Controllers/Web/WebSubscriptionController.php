<?php

namespace App\Http\Controllers\Web;

use App\DTO\Payment\PaymentCreationData;
use App\Enums\Subscription\SubscriptionStatusEnum;
use App\Helpers\PricingHelper;
use App\Http\Controllers\Controller;
use App\Models\Duration;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\WebUser;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;

class WebSubscriptionController extends Controller
{
    public function __construct(private PaymentService $paymentService)
    {
    }

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

    public function createPayment(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|integer|exists:plans,id',
            'duration_id' => 'required|integer|exists:durations,id',
        ]);

        /** @var WebUser $webUser */
        $webUser = $request->user();

        $plan = Plan::findOrFail($request->integer('plan_id'));
        $duration = Duration::findOrFail($request->integer('duration_id'));
        $calculated = PricingHelper::calculateDiscountedPrice($plan->price, $duration->days, $duration->discount_percentage);

        $subscription = Subscription::query()->firstOrCreate(
            ['web_user_id' => $webUser->id],
            [
                'token' => null,
                'telegraph_chat_id' => null,
                'plan_id' => $plan->id,
                'duration_id' => $duration->id,
                'status' => SubscriptionStatusEnum::BLOCKED,
                'end_datetime' => now(),
            ]
        );

        $payment = $this->paymentService->createPayment(new PaymentCreationData(
            plan: $plan,
            duration: $duration,
            price: $calculated['discountedPrice'],
            chatId: null,
            metadata: [
                'source' => 'web',
                'subscription_id' => $subscription->id,
            ]
        ));

        return response()->json([
            'success' => true,
            'payment_url' => $payment['payment_url'] ?? null,
            'payment_id' => $payment['id'] ?? null,
            'amount' => $calculated['discountedPrice'],
        ]);
    }
}
