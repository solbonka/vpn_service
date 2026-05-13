<?php

namespace App\Telegraph\Handlers;

use DefStudio\Telegraph\DTO\CallbackQuery;
use DefStudio\Telegraph\Models\TelegraphBot;
use DefStudio\Telegraph\Models\TelegraphChat;

abstract class BaseMessageHandler
{
    protected TelegraphBot $bot;
    protected TelegraphChat $chat;
    protected ?CallbackQuery $callbackQuery;

    public function __construct(TelegraphBot $bot, TelegraphChat $chat, ?CallbackQuery $callbackQuery = null)
    {
        $this->bot = $bot;
        $this->chat = $chat;
        $this->callbackQuery = $callbackQuery;

        $this->initialize();
    }

    protected function initialize(): void
    {}

    abstract public function canHandle(string $message): bool;
    abstract public function handle(string $message): void;
}
