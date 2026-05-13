<?php

namespace App\Actions\Subscription;

use App\DTO\Actions\Subscription\UpdateSubscriptionActionDto;
use App\Models\Subscription;
use Illuminate\Support\Facades\Log;

class UpdateSubscriptionAction
{
    public function execute(UpdateSubscriptionActionDto $data, Subscription $subscription): ?Subscription
    {
        Log::info('UpdateSubscriptionAction::execute started', [
            'subscription_id' => $subscription->id,
            'update_data' => $data->all(),
            'current_subscription_data' => [
                'status' => $subscription->status->value,
                'plan_id' => $subscription->plan_id,
                'duration_id' => $subscription->duration_id,
                'end_datetime' => $subscription->end_datetime
            ]
        ]);

        $updateResult = $subscription->update($data->all());

        Log::info('UpdateSubscriptionAction::execute result', [
            'subscription_id' => $subscription->id,
            'update_success' => $updateResult,
            'updated_subscription_data' => [
                'status' => $subscription->status->value,
                'plan_id' => $subscription->plan_id,
                'duration_id' => $subscription->duration_id,
                'end_datetime' => $subscription->end_datetime
            ]
        ]);

        if (! $updateResult) {
            Log::error('Failed to update subscription in database', [
                'subscription_id' => $subscription->id,
                'update_data' => $data->all()
            ]);
            return null;
        }

        return $subscription;
    }
}
