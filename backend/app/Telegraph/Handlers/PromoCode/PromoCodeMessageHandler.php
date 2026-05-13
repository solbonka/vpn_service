<?php

namespace App\Telegraph\Handlers\PromoCode;

use App\Services\PromoCode\PromoCodeService;
use App\Telegraph\Handlers\BaseMessageHandler;
use DefStudio\Telegraph\Models\TelegraphBot;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PromoCodeMessageHandler extends BaseMessageHandler
{
    public function __construct(TelegraphBot $bot, TelegraphChat $chat)
    {
        parent::__construct($bot, $chat);
    }

    public function canHandle(string $message): bool
    {
        // Проверяем, ожидаем ли мы ввод промокода от этого пользователя
        $context = Cache::get("promo_code_context:{$this->chat->id}");
        
        Log::info('PromoCodeMessageHandler: Checking if can handle', [
            'chat_id' => $this->chat->id,
            'message' => $message,
            'context' => $context,
            'can_handle' => $context && ($context['awaiting_promo_code'] ?? false)
        ]);
        
        return $context && ($context['awaiting_promo_code'] ?? false);
    }

    public function handle(string $message): void
    {
        Log::info('PromoCodeMessageHandler: Handle called', [
            'chat_id' => $this->chat->id,
            'message' => $message
        ]);

        $promoCodeService = app(PromoCodeService::class);
        
        app(ProcessPromoCodeHandler::class, [
            'chat' => $this->chat,
            'promoCodeInput' => $message,
            'promoCodeService' => $promoCodeService
        ])->handle();
    }
}

