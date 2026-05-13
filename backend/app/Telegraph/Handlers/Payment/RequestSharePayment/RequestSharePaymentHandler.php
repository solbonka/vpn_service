<?php

namespace App\Telegraph\Handlers\Payment\RequestSharePayment;

use App\Helpers\DeleteMessageHelper;
use App\Helpers\Params\ParamsHelper;
use App\Models\Payment;
use App\Services\Payment\PaymentShareService;
use App\Traits\Error\ErrorMessageTrait;
use DefStudio\Telegraph\DTO\CallbackQuery;
use DefStudio\Telegraph\Models\TelegraphChat;
use Exception;
use Illuminate\Support\Facades\Log;

class RequestSharePaymentHandler
{
    use ErrorMessageTrait;

    private TelegraphChat $chat;
    private CallbackQuery $callbackQuery;
    private PaymentShareService $shareService;

    public function __construct(
        TelegraphChat $chat,
        CallbackQuery $callbackQuery,
        PaymentShareService $shareService
    )
    {
        $this->chat = $chat;
        $this->callbackQuery = $callbackQuery;
        $this->shareService = $shareService;
    }

    public function handle(): void
    {
        try {
            DeleteMessageHelper::delete($this->chat, $this->callbackQuery);

            $data = $this->callbackQuery->data();


            $paymentId = $data['payment_id'] ?? null;

            if (!$paymentId) {
                throw new Exception('payment_id не найден в callback data');
            }

            $payment = Payment::where('id', $paymentId)->first();

            if (!$payment) {
                throw new Exception('Платеж не найден');
            }

            $shareLink = $this->shareService->createShareLinkForPayment($payment->id);
            $shareUrl = $shareLink['share_url'];

            Log::info('Share link created in bot', [
                'payment_id' => $payment->id,
                'share_url' => $shareUrl,
                'chat_id' => $this->chat->id
            ]);

            $this->chat->message(RequestSharePaymentMessage::message($shareUrl))
                ->keyboard(RequestSharePaymentButtons::buttons(
                    $shareUrl
                ))
                ->markdown()
                ->send();

        } catch (Exception $e) {
            Log::error('Ошибка при создании share-ссылки в боте', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'chat_id' => $this->chat->id ?? null
            ]);

            $this->chat->message($this->getErrorMessage())->send();
        }
    }
}

