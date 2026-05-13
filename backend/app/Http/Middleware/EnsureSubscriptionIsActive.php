<?php

namespace App\Http\Middleware;

use App\Enums\Subscription\SubscriptionStatusEnum;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSubscriptionIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $subscription = $request->route('subscription');

        if (! $subscription || $subscription->status !== SubscriptionStatusEnum::ACTIVE) {
            return response('Подписка неактивна', 403);
        }

        return $next($request);
    }
}
