<?php

namespace App\Telegraph\Handlers\Connect;

use App\Telegraph\Handlers\BaseMessageHandler;
use App\Telegraph\Handlers\Connect\Steps\ChannelSubscription\ChannelSubscriptionHandler;
use Exception;

class ConnectButtonHandler extends BaseMessageHandler
{
    public function canHandle(string $message): bool
    {
        return $message === '⚡️ Подключиться!';
    }

    /**
     * @throws Exception
     */
    public function handle(string $message): void
    {
        app(ChannelSubscriptionHandler::class, [
            'bot' => $this->bot,
            'chat' => $this->chat,
        ])->handle();
    }
}
