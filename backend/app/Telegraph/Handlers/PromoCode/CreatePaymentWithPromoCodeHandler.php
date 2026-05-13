<?php

namespace App\Telegraph\Handlers\PromoCode;

use App\DTO\Payment\PaymentCreationData;
use App\Helpers\DeleteMessageHelper;
use App\Helpers\PricingHelper;
use App\Models\Duration;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\Payment\PaymentService;
use App\Services\PromoCode\PromoCodeService;
use App\Telegraph\Handlers\Extension\Steps\Payment\CreatePayment\CreatePaymentButtons;
use App\Telegraph\Handlers\Extension\Steps\Payment\CreatePayment\CreatePaymentMessage;
use App\Traits\Error\ErrorMessageTrait;
use DefStudio\Telegraph\DTO\CallbackQuery;
use DefStudio\Telegraph\Models\TelegraphChat;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CreatePaymentWithPromoCodeHandler
{
    use ErrorMessageTrait;

    private TelegraphChat $chat;
    private CallbackQuery $callbackQuery;
    private PaymentService $paymentService;
    private PromoCodeService $promoCodeService;

    public function __construct(
        TelegraphChat $chat,
        CallbackQuery $callbackQuery,
        PaymentService $paymentService,
        PromoCodeService $promoCodeService
    )
    {
        $this->chat = $chat;
        $this->callbackQuery = $callbackQuery;
        $this->paymentService = $paymentService;
        $this->promoCodeService = $promoCodeService;
    }

    public function handle(): void
    {
        try {
            DeleteMessageHelper::delete($this->chat, $this->callbackQuery);

            $data = $this->callbackQuery->data();

            $planId = $data['plan_id'] ?? null;
            $durationId = $data['duration_id'] ?? null;

            // Получаем промокод из Cache
            $promoCodeData = Cache::get("applied_promo_code:{$this->chat->id}");
            $promoCodeInput = $promoCodeData['promo_code'] ?? null;

            Log::info('CreatePaymentWithPromoCodeHandler: Retrieved promo code from cache', [
                'chat_id' => $this->chat->id,
                'promo_code_data' => $promoCodeData,
                'promo_code' => $promoCodeInput
            ]);

            $plan = Plan::query()->find($planId);
            $duration = Duration::query()->find($durationId);

            if (!$plan || !$duration || !$promoCodeInput) {
                Log::warning('CreatePaymentWithPromoCodeHandler: Missing data', [
                    'plan' => $plan?->id,
                    'duration' => $duration?->id,
                    'promo_code' => $promoCodeInput
                ]);
                $this->chat->message($this->getErrorMessage())->send();
                return;
            }

            $subscription = Subscription::query()->where('telegraph_chat_id', $this->chat->id)->first();

            if (!$subscription) {
                $this->chat->message($this->getErrorMessage())->send();
                return;
            }

            // Рассчитываем базовую цену
            $calculated = PricingHelper::calculateDiscountedPrice(
                $plan->price,
                $duration->days,
                $duration->discount_percentage
            );

            // Валидируем промокод еще раз (на всякий случай)
            $validationResult = $this->promoCodeService->validatePromoCode(
                $promoCodeInput,
                $subscription->id,
                $duration->id
            );

            if (!$validationResult['valid']) {
                $this->chat->message("❌ Промокод больше недоступен: {$validationResult['error']}")->send();
                return;
            }

            $promoCode = $validationResult['promo_code'];

            // Рассчитываем скидку от промокода
            $promoDiscount = $this->promoCodeService->calculateDiscount(
                $promoCode,
                $calculated['discountedPrice']
            );

            $finalAmount = $promoDiscount['final_amount'];

            // Создаем платеж
            $payment = $this->paymentService->createPayment(PaymentCreationData::fromArray([
                'plan_id' => $plan->id,
                'duration_id' => $duration->id,
                'price' => $finalAmount,
                'chat_id' => $this->chat->id
            ]));

            $paymentDb = Payment::where('yookassa_payment_id', $payment['id'])->first();

            if ($paymentDb) {
                // Сохраняем промокод в платеже и применяем его
                $paymentDb->update(['promo_code_id' => $promoCode->id]);
                
                $this->promoCodeService->applyPromoCode(
                    $promoCode,
                    $subscription->id,
                    $promoDiscount['original_amount'],
                    $paymentDb->id
                );
            }

            // Очищаем промокод из Cache
            Cache::forget("applied_promo_code:{$this->chat->id}");

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
            Log::error('Ошибка при создании платежа с промокодом', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'chat_id' => $this->chat->id ?? null
            ]);

            $this->chat->message($this->getErrorMessage())->send();
        }
    }
}

