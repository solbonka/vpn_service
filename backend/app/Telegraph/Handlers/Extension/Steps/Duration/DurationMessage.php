<?php

namespace App\Telegraph\Handlers\Extension\Steps\Duration;

class DurationMessage
{
    public static function message(): string
    {
        return "
⏳ *Выберите продолжительность подписки*

Укажите, на какой срок вы хотите оформить подписку.
Действует система скидок: чем дольше срок — тем выгоднее!

Ниже представлены доступные варианты с учетом скидки.
";
    }
}
