<?php

namespace App\Actions\Subscription;

use App\DTO\Actions\Subscription\StoreSubscriptionActionDto;
use App\Models\Subscription;

class StoreSubscriptionAction
{
    public function execute(StoreSubscriptionActionDto $data): ?Subscription
    {
        return Subscription::query()->create($data->all());
    }
}
