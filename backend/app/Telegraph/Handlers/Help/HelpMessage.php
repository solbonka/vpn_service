<?php

namespace App\Telegraph\Handlers\Help;

class HelpMessage
{
    public static function message(): string
    {
        $supportChanelName = config('telegram.support_chanel_name');

        return "
❓ Нужна помощь?

💬 Поддержка 24/7 — мы всегда готовы помочь!

📩 Если у Вас возникли вопросы по подключению - обратитесь в поддержку $supportChanelName

🔍 А пока попробуйте:

— Перезапустить приложение

— Проверить настройки подключения

— Убедиться, что подписка активна
";
    }
}
