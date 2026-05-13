<?php

namespace App\Telegraph\Handlers\Help;

use App\Telegraph\Handlers\BaseMessageHandler;

class HelpButtonHandler extends BaseMessageHandler
{
    public function canHandle(string $message): bool
    {
        return $message === '🆘 Помощь';

    }

    public function handle(string $message): void
    {
        $this->chat->message(HelpMessage::message())
            ->send();
    }
}
