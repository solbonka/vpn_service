<?php

namespace App\Telegraph\Handlers\Connect\Steps\Guides\InstallAppGuide;

use App\Helpers\DeleteMessageHelper;
use DefStudio\Telegraph\DTO\CallbackQuery;
use DefStudio\Telegraph\Models\TelegraphChat;

class AppGuideHandler
{
    private TelegraphChat $chat;
    private CallbackQuery $callbackQuery;
    private string $domain;

    public function __construct(TelegraphChat $chat, CallbackQuery $callbackQuery) {
        $this->chat = $chat;
        $this->callbackQuery = $callbackQuery;

        $this->domain = config('telegram.domain');
    }

    public function handle(): void
    {
        DeleteMessageHelper::delete($this->chat, $this->callbackQuery);

        $osId = $this->callbackQuery->data()['os_id'];

        $this->chat->message(AppGuideMessage::message())
            ->withoutPreview()
            ->markdown()
            ->keyboard(AppGuideButtons::buttons($this->domain, $osId))
            ->send();
    }
}
