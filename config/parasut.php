<?php

return [
    'connection' => [
        'username'      => env('PARASUT_USERNAME'),
        'password'      => env('PARASUT_PASSWORD'),
        'is_stage'      => env('PARASUT_IS_STAGE', false),
        'client_id'     => env('PARASUT_CLIENT_ID'),
        'company_id'    => env('PARASUT_COMPANY_ID'),
        'redirect_uri'  => env('PARASUT_REDIRECT_URI', 'urn:ietf:wg:oauth:2.0:oob'),
        'client_secret' => env('PARASUT_CLIENT_SECRET'),
    ],
    'account_id'          => env('PARASUT_ACCOUNT_ID'),
    'invoice_series'      => env('PARASUT_INVOICE_SERIES', 'AIBC'),
    'vat_exemption_code'  => env('PARASUT_VAT_EXEMPTION_CODE', '335'),
    'auto_formalize'      => env('PARASUT_AUTO_FORMALIZE', false),
    'formalize_days'      => env('PARASUT_FORMALIZE_DAYS', 3),
];
