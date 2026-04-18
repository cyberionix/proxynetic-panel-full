<?php

return [
    'client_id'   => env('NESTPAY_CLIENT_ID', ''),
    'store_key'   => env('NESTPAY_STORE_KEY', ''),
    'gateway_url' => env('NESTPAY_GATEWAY_URL', 'https://istest.asseco-see.com.tr/fim/est3Dgate'),
    'enabled'     => env('NESTPAY_ENABLED', false),
];
