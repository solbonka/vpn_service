<?php

namespace App\Services\Payment;

use App\Models\Payment;
use Exception;
use Illuminate\Support\Facades\Log;

class PaymentShareService
{
    /**
     * Создать share-токен для существующего платежа
     */
    public function createShareLinkForPayment(int $paymentId): array
    {
        $payment = Payment::with(['subscription.plan', 'subscription.duration'])
            ->findOrFail($paymentId);

        // Проверяем, что платеж можно оплатить
        if (!$payment->isPayable()) {
            throw new Exception('Этот платеж уже оплачен или недействителен');
        }

        // Генерируем токен (или возвращаем существующий)
        $token = $payment->createShareToken();

        Log::info('Payment share token created', [
            'payment_id' => $payment->id,
            'token' => $token
        ]);

        return [
            'token' => $token,
            'share_url' => $payment->getShareUrl()
        ];
    }

    /**
     * Получить информацию о платеже по share-токену
     */
    public function getPaymentByShareToken(string $token): array
    {
        $payment = Payment::with(['subscription.plan', 'subscription.duration'])
            ->where('share_token', $token)
            ->firstOrFail();

        // Увеличиваем счетчик просмотров
        $payment->incrementShareViews();

        $subscription = $payment->subscription;

        return [
            'payment_id' => $payment->id,
            'yookassa_payment_id' => $payment->yookassa_payment_id,
            'payment_url' => $payment->payment_url,
            'amount' => $payment->amount,
            'currency' => $payment->currency ?? 'RUB',
            'status' => $payment->status->value,
            'is_payable' => $payment->isPayable(),
            'plan' => [
                'id' => $subscription->plan->id ?? null,
                'name' => $subscription->plan->name ?? 'VPN подписка'
            ],
            'duration' => [
                'id' => $subscription->duration->id ?? null,
                'name' => $subscription->duration->name ?? ''
            ],
            'views_count' => $payment->share_views_count
        ];
    }
}


