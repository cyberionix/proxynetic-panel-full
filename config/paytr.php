<?php
/**
 * PayTR konfigürasyonu — env tabanlı.
 * test_mode, success/fail/callback URL'leri admin panel "System Settings"den yönetilir.
 */
return [
    'enabled' => env('PAYTR_ENABLED', false),
    'credentials' => [
        'merchant_id'   => env('PAYTR_MERCHANT_ID', ''),
        'merchant_salt' => env('PAYTR_MERCHANT_SALT', ''),
        'merchant_key'  => env('PAYTR_MERCHANT_KEY', ''),
    ],
    'options' => [
        'base_uri'           => env('PAYTR_BASE_URI', 'https://www.paytr.com'),
        'timeout'            => (int) env('PAYTR_TIMEOUT', 60),
        'iframe_timeout_min' => (int) env('PAYTR_IFRAME_TIMEOUT_MIN', 30),
        'success_url'        => env('PAYTR_SUCCESS_URL', ''),
        'fail_url'           => env('PAYTR_FAIL_URL', ''),
        'callback_url'       => env('PAYTR_CALLBACK_URL', ''),
        'test_mode'          => filter_var(env('PAYTR_TEST_MODE', true), FILTER_VALIDATE_BOOLEAN),
        'debug_on'           => (int) env('PAYTR_DEBUG_ON', 1),
    ],
    /*
     * Test Mode kart bilgileri (PayTR resmi test kartı)
     * Test Card Number: 4355 0843 5508 4358
     * Expiry: 12/26 - CVV: 000 - Cardholder: PAYTR TEST
     */
    'test_cards' => [
        ['name' => 'PAYTR TEST',  'number' => '4355084355084358', 'expiry' => '12/30', 'cvv' => '000', 'note' => 'Visa - Başarılı'],
        ['name' => 'PAYTR TEST',  'number' => '5406675406675403', 'expiry' => '12/30', 'cvv' => '000', 'note' => 'MasterCard - Başarılı'],
        ['name' => 'PAYTR TEST',  'number' => '9792030394440796', 'expiry' => '12/30', 'cvv' => '000', 'note' => 'Troy - Başarılı'],
    ],
];
