<?php

namespace App\Actions\Payment;

use App\DTO\Actions\Payment\StorePaymentActionDto;
use App\Models\Payment;

class StorePaymentAction
{
    public function execute(StorePaymentActionDto $data): ?Payment
    {
        return Payment::query()->create($data->all());
    }
}
