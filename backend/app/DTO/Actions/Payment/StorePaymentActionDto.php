<?php

namespace App\DTO\Actions\Payment;



readonly class StorePaymentActionDto
{
    public function __construct(
        public int $subscriptionId,
        public string $yookassaPaymentId,
        public string $amount,
        public ?string $paymentUrl = null
    ) {
    }

    public function all(): array
    {
        return [
            'subscription_id' => $this->subscriptionId,
            'yookassa_payment_id' => $this->yookassaPaymentId,
            'amount' => $this->amount,
            'payment_url' => $this->paymentUrl
        ];
    }
}
