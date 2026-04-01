<?php

$localtonetHttpVerify = filter_var(env('LOCALTONET_HTTP_VERIFY', true), FILTER_VALIDATE_BOOLEAN);
$localtonetSettingsPath = __DIR__.'/localtonet_settings.php';
if (is_file($localtonetSettingsPath)) {
    $localtonetPanel = require $localtonetSettingsPath;
    if (is_array($localtonetPanel) && array_key_exists('http_verify', $localtonetPanel)) {
        $localtonetHttpVerify = (bool) $localtonetPanel['http_verify'];
    }
}

$localtonetV4Panel = [
    'http_port' => 8888,
    'socks_port' => 9999,
    'random_port_min' => 20000,
    'random_port_max' => 65000,
    'tunnel_net_interface' => env('LOCALTONET_V4_TUNNEL_NET', 'Ethernet0'),
    // Localtonet API v2 PATCH .../protocol-type gövdesi (CreateProxyTunnel ile aynı: 6=HTTP, 7=SOCKS5). Farklı API sürümü için .env ile değiştirin.
    'v2_protocol_http' => (int) env('LOCALTONET_V2_PROTOCOL_HTTP', 6),
    'v2_protocol_socks' => (int) env('LOCALTONET_V2_PROTOCOL_SOCKS', 7),
    // v2 bulk: tek istekte en fazla kaç tünel (API limiti)
    'bulk_proxy_chunk_size' => max(1, min(100, (int) env('LOCALTONET_V4_BULK_CHUNK', 100))),
    // Bu adet ve üzeri teslimatta bulk endpoint kullanılır
    'bulk_proxy_min_count' => max(1, (int) env('LOCALTONET_V4_BULK_MIN', 10)),
];
$localtonetV4Path = __DIR__.'/localtonet_v4_panel.php';
if (is_file($localtonetV4Path)) {
    $loaded = require $localtonetV4Path;
    if (is_array($loaded)) {
        $localtonetV4Panel = array_merge($localtonetV4Panel, $loaded);
    }
}

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'mailjet' => [
        'key' => env('MAILJET_APIKEY'),
        'secret' => env('MAILJET_APISECRET'),
        'transactional' => true,
        'call' => true,
        'options' => [
            'url' => 'api.mailjet.com',
            'version' => 'v3.1',
            'call' => true
        ],
    ],

    'sms' => [
        'enabled' => env('SMS_ENABLED', true),
        'iletimerkezi' => [
            'key' => 'c4981e0b2dcf74f316d4a4547a9ca781',
            'secret' => '0f96379e60475eba2f0b93dac22f6656ec6b198c78020817d9759ff507dd9e67',
            'origin' => 'IngBilCocuk',
            'enable' => true,
            'debug' =>  true, //will log sending attempts and results
            'sandboxMode' => false //will not invoke API call
        ],
    ],
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    "qnb_vpos" => [
        'merchant_id' => '143400000005918',
        'merchant_password' => '68244381',
        'user_code' => 'NetpusYazilim',
        'user_password' => '8Z4Gl',
        'secure_type' => '3DPay',
    ],

    'localtonet' => [
        'http_verify' => $localtonetHttpVerify,
    ],

    'localtonet_v4' => $localtonetV4Panel,

];
