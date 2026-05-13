<?php

namespace App\Telegraph\Handlers\Connect\Steps\OperatingSystem;

use App\Helpers\DeleteMessageHelper;
use App\Models\ClientApp;
use DefStudio\Telegraph\DTO\CallbackQuery;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Models\TelegraphChat;

class OperatingSystemHandler
{
    private TelegraphChat $chat;
    private ?CallbackQuery $callbackQuery;

    public function __construct(TelegraphChat $chat, ?CallbackQuery $callbackQuery = null) {
        $this->chat = $chat;
        $this->callbackQuery = $callbackQuery;
    }

    public function handle(): void
    {
        if ($this->callbackQuery) {
            DeleteMessageHelper::delete($this->chat, $this->callbackQuery);
        }

        $allActiveOS = ClientApp::with('activeOperatingSystems')
            ->get()
            ->pluck('activeOperatingSystems')
            ->flatten()
            ->sortBy('id');

        $keyboard = Keyboard::make();

        foreach ($allActiveOS as $os) {
            OperatingSystemButtons::buttons($keyboard, $os);
        }

        $this->chat->message(OperatingSystemMessage::message())
            ->markdown()
            ->keyboard($keyboard)
            ->send();
    }
}
