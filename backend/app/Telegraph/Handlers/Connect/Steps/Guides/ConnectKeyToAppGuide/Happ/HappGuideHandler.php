<?php

namespace App\Telegraph\Handlers\Connect\Steps\Guides\ConnectKeyToAppGuide\Happ;

use DefStudio\Telegraph\Models\TelegraphChat;

class HappGuideHandler
{
    private TelegraphChat $chat;
    private string $domain;

    public function __construct(TelegraphChat $chat) {
        $this->chat = $chat;
        $this->domain = config('telegram.domain');
    }

    public function handle(): void
    {
        $this->chat->message(HappGuideMessage::message())
            ->withoutPreview()
            ->markdown()
            ->keyboard(HappGuideButtons::buttons($this->domain))
            ->send();
    }

}
