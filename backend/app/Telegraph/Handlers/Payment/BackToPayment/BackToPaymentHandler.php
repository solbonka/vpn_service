<?php

namespace App\Telegraph\Handlers\Payment\BackToPayment;

use App\Helpers\DeleteMessageHelper;
use App\Telegraph\Handlers\Extension\Steps\Payment\CreatePayment\CreatePaymentButtons;
use App\Telegraph\Handlers\Extension\Steps\Payment\CreatePayment\CreatePaymentMessage;
use App\Traits\Error\ErrorMessageTrait;
use DefStudio\Telegraph\DTO\CallbackQuery;
use DefStudio\Telegraph\Models\TelegraphChat;
use Exception;
use Illuminate\Support\Facades\Log;

class BackToPaymentHandler
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
        try {
            // Удаляем предыдущее сообщение
            DeleteMessageHelper::delete($this->chat, $this->callbackQuery);

            $data = $this->callbackQuery->data();

            $paymentId = $data['payment_id'] ?? null;
            $paymentUrl = $data['payment_url'] ?? null;
            $backData = $data['data'] ?? '';

            if (!$paymentId || !$paymentUrl) {
                throw new Exception('Отсутствуют данные платежа');
            }

            // Отправляем исходное сообщение и кнопки
            $this->chat->message(CreatePaymentMessage::message())
                ->keyboard(CreatePaymentButtons::buttons(
                    $paymentUrl,
                    $paymentId,
                    $backData
                ))
                ->markdown()
                ->send();

        } catch (Exception $e) {
            Log::error('Ошибка при возврате к платежу', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'chat_id' => $this->chat->id ?? null
            ]);

            $this->chat->message($this->getErrorMessage())->send();
        }
    }
}

