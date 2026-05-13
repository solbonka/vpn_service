<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use YooKassa\Model\Notification\NotificationCanceled;
use YooKassa\Model\Notification\NotificationEventType;
use YooKassa\Model\Notification\NotificationSucceeded;
use YooKassa\Model\Notification\NotificationWaitingForCapture;

class PaymentController extends Controller
{
    private PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function callback(Request $request): Response
    {
        try {
            $requestData = json_decode($request->getContent(), true);

            $eventType = $requestData['event'] ?? null;

            $notification = match ($eventType) {
                NotificationEventType::PAYMENT_SUCCEEDED => new NotificationSucceeded($requestData),
                NotificationEventType::PAYMENT_CANCELED => new NotificationCanceled($requestData),
                NotificationEventType::PAYMENT_WAITING_FOR_CAPTURE => new NotificationWaitingForCapture($requestData),
                default => throw new InvalidArgumentException("Неподдерживаемый тип события: $eventType")
            };

            $payment = $notification->getObject();
            $this->paymentService->callbackPayment($payment);

            Log::info('Payment webhook received', ['data' => $requestData]);

            return response('OK', 200);
        } catch (\Exception $e) {
            Log::error('Webhook processing failed', [
                'error' => $e->getMessage()
            ]);

            return response('Error', 400);
        }
    }
}
