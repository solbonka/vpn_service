<?php

namespace App\Telegraph\Handlers\Extension\Steps\Payment\CreatePayment;

use App\DTO\Payment\PaymentCreationData;
use App\Helpers\DeleteMessageHelper;
use App\Helpers\PricingHelper;
use App\Models\Duration;
use App\Models\Payment;
use App\Models\Plan;
use App\Services\Payment\PaymentService;
use App\Traits\Error\ErrorMessageTrait;
use DefStudio\Telegraph\DTO\CallbackQuery;
use DefStudio\Telegraph\Models\TelegraphChat;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CreatePaymentHandler
{
    use ErrorMessageTrait;

    private TelegraphChat $chat;
    private CallbackQuery $callbackQuery;
    private PaymentService $paymentService;

    public function __construct(
        TelegraphChat  $chat,
        CallbackQuery  $callbackQuery,
        PaymentService $paymentService
    )
    {
        $this->chat = $chat;
        $this->callbackQuery = $callbackQuery;
        $this->paymentService = $paymentService;
    }

    public function handle(): void
    {
        try {
            DeleteMessageHelper::delete($this->chat, $this->callbackQuery);

            $data = $this->callbackQuery->data();

            $planId = $data['plan_id'] ?? null;
            $durationId = $data['duration_id'] ?? null;

            $plan = Plan::query()->find($planId);
            $duration = Duration::query()->find($durationId);


            $calculated = PricingHelper::calculateDiscountedPrice(
                $plan->price,
                $duration->days,
                $duration->discount_percentage
            );

            $payment = $this->paymentService->createPayment(PaymentCreationData::fromArray([
                'plan_id' => $plan->id,
                'duration_id' => $duration->id,
                'price' => $calculated['discountedPrice'],
                'chat_id' => $this->chat->id
            ]));

            $paymentDb = Payment::where('yookassa_payment_id', $payment['id'])->first();

            $response = $this->chat->message(CreatePaymentMessage::message())
                ->keyboard(CreatePaymentButtons::buttons(
                    $payment['payment_url'],
                    $paymentDb->id,
                    $data['data']
                ))
                ->markdown()
                ->send();

            Cache::put("payment-message:{$payment['id']}", [
                'message_id' => $response->telegraphMessageId()
            ], now()->addMinutes(30));

        } catch (Exception $e) {
            Log::error('Ошибка при создании платежа', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'chat_id' => $this->chat->id ?? null
            ]);

            $this->chat->message($this->getErrorMessage())->send();
        }
    }
}
