<?php

namespace App\Jobs\Chat\NotSubscription;

class NotifyNotSubscriptionToChatMessage
{
    public static function message(): string
    {
        return "
Мы заметили, что вы еще не используете наш VPN 😞

Возможно у вас возникли ошибки с получением подписки,
давайте мы поможем вам это исправить, всего лишь нажмите
на кнопку ниже 👇, после нажатия на которую вы сможете пользоваться VPN
";
    }
}
