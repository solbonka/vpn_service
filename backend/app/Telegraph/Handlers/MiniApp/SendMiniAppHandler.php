<?php

namespace App\Telegraph\Handlers\MiniApp;

use App\Telegraph\Handlers\BaseMessageHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;

class SendMiniAppHandler extends BaseMessageHandler
{
    public function canHandle(string $message): bool
    {
        return $message === '📱 Открыть приложение';
    }

    /**
     * @throws Exception
     */
    public function handle(string $message): void
    {
        $this->chat->message('Воспользуйтесь нашим мини-приложением в ТГ: ')
            ->markdown()
            ->keyboard(
                Keyboard::make()->row([
                    Button::make('Приложение')->webApp(config('telegram.mini_app_domain'))
                ])
            )
            ->send();
    }
}
