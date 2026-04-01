<?php
return [
    'name' => env('BRAND_NAME', 'Proxynetic'),
    'clientarea_title' => env('BRAND_CUSTOMER_PANEL_TITLE'),
    'base_url' => env('BASE_APP_URL'),
    'url' => 'www.proxynetic.com',
    'name_short' => env('APP_URL'),
    'logo' => env('BRAND_LOGO_PATH'),
    'logo_dark' => env('BRAND_LOGO_DARK'),
    'favicon' => env('BRAND_FAVICON'),
    'contact_info' => [
        'phone_number' => env('BRAND_INFO_PHONE_NUMBER'),
        'email' => env('BRAND_INFO_EMAIL'),
        'address_line_1' => env('BRAND_ADDRESS_LINE_1'),
        'address_line_2' => env('BRAND_ADDRESS_LINE_2'),
        'website' => env('BRAND_WEBSITE')
    ],
    'social_media' => [
        'facebook' => env('BRAND_FACEBOOK'),
        'twitter' => env('BRAND_TWITTER'),
        'instagram' => env('BRAND_INSTAGRAM'),
        'linkedin' => env('BRAND_LINKEDIN'),
        'youtube' => env('BRAND_YOUTUBE')
    ]
];
