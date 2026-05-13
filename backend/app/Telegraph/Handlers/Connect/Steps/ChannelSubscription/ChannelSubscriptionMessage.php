<?php

namespace App\Telegraph\Handlers\Connect\Steps\ChannelSubscription;

class ChannelSubscriptionMessage
{
    public static function message(string $chanelName): string
    {
        return "*Для бесплатного подключения подпишитесь на наш канал: $chanelName*";
    }
}
