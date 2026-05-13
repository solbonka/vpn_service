<?php

namespace App\Telegraph\Handlers\Connect\Steps\Guides\ConnectKeyToAppGuide\V2RayTun;

use DefStudio\Telegraph\Models\TelegraphChat;

class V2RayTunGuideHandler
{
    private TelegraphChat $chat;
    private string $domain;

    public function __construct(TelegraphChat $chat) {
        $this->chat = $chat;
        $this->domain = config('telegram.domain');
    }

    public function handle(): void
    {
        $this->chat->message(V2RayTunGuideMessage::message())
            ->withoutPreview()
            ->markdown()
            ->keyboard(V2RayTunGuideButtons::buttons($this->domain))
            ->send();
    }
}
