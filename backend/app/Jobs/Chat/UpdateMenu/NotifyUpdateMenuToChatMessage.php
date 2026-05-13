<?php

namespace App\Jobs\Chat\UpdateMenu;

class NotifyUpdateMenuToChatMessage
{
    public static function message(): string
    {
        return "
Мы немного обновили меню!

Чтобы оно обновилось у вас, нажмите на кнопку ниже
";
    }
}
