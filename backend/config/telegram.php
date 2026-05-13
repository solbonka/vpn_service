<?php

return [
    'bot_token' => env('TG_BOT_TOKEN', ''),

    'show_miniapp_button' => env('TELEGRAM_SHOW_MINIAPP_BUTTON', false),

    'show_author_button' => env('TELEGRAM_SHOW_AUTHOR_BUTTON', true),

    'support_chanel_name' => env('TELEGRAM_SUPPORT_CHANEL_NAME', '@your_support_channel'),

    'support_channel_link' => env('TELEGRAM_SUPPORT_CHANEL_LINK', 't.me/support_channel_link'),

    'chanel_name' => env('TELEGRAM_CHANEL_NAME', '@your_channel'),

    'chanel_link' => env('TELEGRAM_CHANEL_LINK', 't.me/your_channel_link'),

    'bot_link' => env('TELEGRAM_BOT_LINK', 't.me/your_bot_link'),

    'check_subscription_to_chanel' => env('CHECK_SUBSCRIPTION_TO_CHANEL', true),

    'domain' => env('TELEGRAM_DOMAIN', ''),

    'mini_app_domain' => env('TELEGRAM_MINI_APP_DOMAIN', ''),

    'error_bot_token' => env('TELEGRAM_ERROR_BOT_TOKEN', ''),
    'error_channel_id' => env('TELEGRAM_ERROR_CHANNEL_ID', ''),
    'error_channel_name' => env('TELEGRAM_ERROR_CHANNEL_NAME', '@error_monitoring'),
    'error_notifications_enabled' => env('TELEGRAM_ERROR_NOTIFICATIONS_ENABLED', true),
    'error_environment_name' => env('TELEGRAM_ERROR_ENVIRONMENT_NAME', ''),

    'notify_passive_enabled' => env('TELEGRAM_NOTIFY_PASSIVE_ENABLED', false),

    'passive_users_channel_id' => env('TELEGRAM_PASSIVE_USERS_CHANNEL_ID', ''),

    'notification_system_start_date' => env('NOTIFICATION_SYSTEM_START_DATE', null),
];
