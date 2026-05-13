<?php

namespace App\Helpers;

use DefStudio\Telegraph\Keyboard\ReplyButton;
use DefStudio\Telegraph\Keyboard\ReplyKeyboard;
use DefStudio\Telegraph\Models\TelegraphChat;

class MenuKeyboardHelper
{
    public static function send(TelegraphChat $chat, string $message): void
    {
        $keyboard = self::buildReplyKeyboard();

        $chat->message($message)
            ->markdown()
            ->replyKeyboard($keyboard)
            ->send();
    }

    private static function buildReplyKeyboard(): ReplyKeyboard
    {
        $keyboard = ReplyKeyboard::make();

        $keyboard->row([
            ReplyButton::make('⚡️ Подключиться!'),
            ReplyButton::make('🔄️ Продлить подписку')
        ]);

        $keyboard->row([
            ReplyButton::make('ℹ️ Статус'),
            ReplyButton::make('🆘 Помощь')
        ]);

        if (config('telegram.show_miniapp_button')) {
            $keyboard->row([
                ReplyButton::make('📱 Открыть приложение')
            ]);
        }

        if (config('telegram.show_author_button')) {
            $keyboard->row([
                ReplyButton::make('🙎🏻‍♂️ Об авторе'),
            ]);
        }

        return $keyboard;
    }
}
