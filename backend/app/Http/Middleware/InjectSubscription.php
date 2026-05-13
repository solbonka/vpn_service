<?php

namespace App\Http\Middleware;

use App\Models\Subscription;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InjectSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $subscriptionToken = $request->header('X-Sub-Token');

        if ($subscriptionToken) {
            $subscription = Subscription::where('token', $subscriptionToken)->first();

            if ($subscription) {
                $request->attributes->set('subscription', $subscription);
            } else {
                abort(Response::HTTP_NOT_FOUND);
            }
        } else {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}

