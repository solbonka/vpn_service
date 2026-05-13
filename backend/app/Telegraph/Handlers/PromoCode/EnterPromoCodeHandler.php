<?php

namespace App\Telegraph\Handlers\PromoCode;

use App\Helpers\DeleteMessageHelper;
use App\Helpers\PricingHelper;
use App\Models\Duration;
use App\Models\Plan;
use App\Traits\Error\ErrorMessageTrait;
use DefStudio\Telegraph\DTO\CallbackQuery;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class EnterPromoCodeHandler
{
    use ErrorMessageTrait;

    private TelegraphChat $chat;
    private CallbackQuery $callbackQuery;

    public function __construct(
        TelegraphChat $chat,
        CallbackQuery $callbackQuery
    )
    {
        $this->chat = $chat;
        $this->callbackQuery = $callbackQuery;
    }

    public function handle(): void
    {
        Log::info('EnterPromoCodeHandler: Handle called', [
            'chat_id' => $this->chat->id,
            'callback_data' => $this->callbackQuery->data()
        ]);

        try {
            DeleteMessageHelper::delete($this->chat, $this->callbackQuery);

            $data = $this->callbackQuery->data();

            $planId = $data['plan_id'] ?? null;
            $durationId = $data['duration_id'] ?? null;
            $originalData = $data['data'] ?? null;

            $plan = Plan::query()->find($planId);
            $duration = Duration::query()->find($durationId);

            if (!$plan || !$duration) {
                $this->chat->message($this->getErrorMessage())->send();
                return;
            }

            $contextData = [
                'plan_id' => $plan->id,
                'duration_id' => $duration->id,
                'data' => $originalData,
                'awaiting_promo_code' => true
            ];

            Cache::put("promo_code_context:{$this->chat->id}", $contextData, now()->addMinutes(5));

            Log::info('EnterPromoCodeHandler: Context saved', [
                'chat_id' => $this->chat->id,
                'context' => $contextData
            ]);

            $message = "
🎫 *Введите промокод*

Отправьте код в следующем сообщении.

_Например: TEST50_
";

            $this->chat->message($message)
                ->keyboard(Keyboard::make()->row([
                    Button::make('❌ Отмена')
                        ->action('selectPaymentMethodAction')
                        ->param('data', $originalData)
                ]))
                ->markdown()
                ->send();

        } catch (\Exception $e) {
            Log::error('Ошибка при запросе промокода', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'chat_id' => $this->chat->id ?? null
            ]);

            $this->chat->message($this->getErrorMessage())->send();
        }
    }
}

