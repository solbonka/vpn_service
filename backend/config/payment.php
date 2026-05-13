<?php

return [
    'enabled' => env('PAYMENT_ENABLED', false),

    'yookassa' => [
        'shopId' => (int) env('YOOKASSA_SHOP_ID', ''),
        'secretKey' => env('YOOKASSA_SECRET_KEY', ''),
        'return_url' => env('YOOKASSA_RETURN_URL', '')
    ],
];
