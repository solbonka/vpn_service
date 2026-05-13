<?php

namespace App\Telegraph\Handlers\PromoCode;

use App\Helpers\PricingHelper;
use App\Models\Duration;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\PromoCode\PromoCodeService;
use App\Traits\Error\ErrorMessageTrait;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Models\TelegraphChat;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProcessPromoCodeHandler
{
    use ErrorMessageTrait;

    private TelegraphChat $chat;
    private string $promoCodeInput;
    private PromoCodeService $promoCodeService;

    public function __construct(
        TelegraphChat $chat,
        string $promoCodeInput,
        PromoCodeService $promoCodeService
    )
    {
        $this->chat = $chat;
        $this->promoCodeInput = trim(strtoupper($promoCodeInput));
        $this->promoCodeService = $promoCodeService;
    }

    public function handle(): void
    {
        try {
            // Получаем контекст из кеша
            $context = Cache::get("promo_code_context:{$this->chat->id}");

            Log::info('ProcessPromoCodeHandler: Checking context', [
                'chat_id' => $this->chat->id,
                'context' => $context,
                'promo_code_input' => $this->promoCodeInput
            ]);

            if (!$context || !($context['awaiting_promo_code'] ?? false)) {
                Log::warning('ProcessPromoCodeHandler: No context or not awaiting promo code');
                // Не ждем промокод, игнорируем
                return;
            }

            // Очищаем контекст
            Cache::forget("promo_code_context:{$this->chat->id}");

            $planId = $context['plan_id'];
            $durationId = $context['duration_id'];
            $originalData = $context['data'];

            $plan = Plan::query()->find($planId);
            $duration = Duration::query()->find($durationId);

            if (!$plan || !$duration) {
                $this->chat->message($this->getErrorMessage())->send();
                return;
            }

            $subscription = Subscription::query()->where('telegraph_chat_id', $this->chat->id)->first();

            if (!$subscription) {
                $this->chat->message($this->getErrorMessage())->send();
                return;
            }

            Log::info('ProcessPromoCodeHandler: Validating promo code', [
                'chat_id' => $this->chat->id,
                'promo_code' => $this->promoCodeInput,
                'subscription_id' => $subscription->id,
                'duration_id' => $duration->id
            ]);

            $validationResult = $this->promoCodeService->validatePromoCode(
                $this->promoCodeInput,
                $subscription->id,
                $duration->id
            );

            Log::info('ProcessPromoCodeHandler: Validation result', [
                'result' => $validationResult
            ]);

            if (!$validationResult['valid']) {
                $errorMessage = "
❌ *Ошибка промокода*

{$validationResult['error']}

Попробуйте другой код или продолжите без промокода.
";

                $this->chat->message($errorMessage)
                    ->keyboard(Keyboard::make()
                        ->row([
                            Button::make('🎫 Ввести другой промокод')
                                ->action('enterPromoCodeAction')
                                ->param('plan_id', $plan->id)
                                ->param('duration_id', $duration->id)
                                ->param('data', $originalData)
                        ])
                        ->row([
                            Button::make('💳 Продолжить без промокода')
                                ->action('createPaymentAction')
                                ->param('plan_id', $plan->id)
                                ->param('duration_id', $duration->id)
                                ->param('data', $originalData)
                        ])
                        ->row([
                            Button::make('◀️ Назад')
                                ->action('selectPaymentMethodAction')
                                ->param('data', $originalData)
                        ])
                    )
                    ->markdown()
                    ->send();

                return;
            }

            // Промокод валиден - рассчитываем цену
            Log::info('ProcessPromoCodeHandler: Promo code is valid, calculating price');

            $calculated = PricingHelper::calculateDiscountedPrice(
                $plan->price,
                $duration->days,
                $duration->discount_percentage
            );

            Log::info('ProcessPromoCodeHandler: Base price calculated', [
                'calculated' => $calculated
            ]);

            $promoCode = $validationResult['promo_code'];
            $discountCalculation = $this->promoCodeService->calculateDiscount(
                $promoCode,
                $calculated['discountedPrice']
            );

            Log::info('ProcessPromoCodeHandler: Discount calculated', [
                'discount_calculation' => $discountCalculation
            ]);

            $originalPrice = $discountCalculation['original_amount'];
            $finalPrice = $discountCalculation['final_amount'];
            $discountPercent = $discountCalculation['discount_percent'];

            $message = "
✅ *Промокод применен!*

🎫 Код: *{$this->promoCodeInput}*
📊 Скидка: *{$discountPercent}%*

✅ Тариф: *{$plan->name}* на *{$duration->name}* ({$duration->days} дней)

💰 Цена без промокода: ~{$originalPrice} руб.~
💳 Сумма к оплате: *{$finalPrice} руб.*

Нажмите \"Оплатить\" для продолжения:
";

            Log::info('ProcessPromoCodeHandler: Sending message', [
                'chat_id' => $this->chat->id,
                'message' => $message
            ]);

            // Сохраняем промокод в Cache для последующего использования
            Cache::put("applied_promo_code:{$this->chat->id}", [
                'promo_code' => $this->promoCodeInput,
                'plan_id' => $plan->id,
                'duration_id' => $duration->id,
                'data' => $originalData
            ], now()->addMinutes(10));

            $response = $this->chat->message($message)
                ->keyboard(Keyboard::make()
                    ->row([
                        Button::make('💳 Оплатить')
                            ->action('payWithPromoAction')
                            ->param('plan_id', $plan->id)
                            ->param('duration_id', $duration->id)
                            ->param('data', $originalData)
                    ])
                    ->row([
                        Button::make('❌ Отменить промокод')
                            ->action('selectPaymentMethodAction')
                            ->param('data', $originalData)
                    ])
                )
                ->markdown()
                ->send();

            Log::info('ProcessPromoCodeHandler: Message sent', [
                'chat_id' => $this->chat->id,
                'response' => $response?->json() ?? 'no response'
            ]);

        } catch (Exception $e) {
            Log::error('Ошибка обработки промокода', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'chat_id' => $this->chat->id ?? null,
                'promo_code' => $this->promoCodeInput
            ]);

            $this->chat->message($this->getErrorMessage())->send();
        }
    }
}

