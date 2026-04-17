<?php

namespace App\Services;

class ShopierService
{
    protected $apiKey;
    protected $apiSecret;
    protected $paymentUrl = 'https://www.shopier.com/ShowProduct/api_pay4.php';
    protected $moduleVersion = '1.0.4';

    public function __construct()
    {
        $this->apiKey = env('SHOPIER_API_KEY');
        $this->apiSecret = env('SHOPIER_API_SECRET');
    }

    public function generatePaymentForm(array $params): string
    {
        $randomNr = rand(100000, 999999);

        $args = [
            'API_key'            => $this->apiKey,
            'website_index'      => 1,
            'platform_order_id'  => $params['order_id'],
            'product_name'       => $params['product_name'],
            'product_type'       => 0, // 0 = downloadable/virtual
            'buyer_name'         => $params['buyer_name'],
            'buyer_surname'      => $params['buyer_surname'],
            'buyer_email'        => $params['buyer_email'],
            'buyer_account_age'  => 0,
            'buyer_id_nr'        => $params['buyer_id'] ?? $params['order_id'],
            'buyer_phone'        => $params['buyer_phone'] ?? '05000000000',
            'billing_address'    => $params['billing_address'] ?? 'Türkiye',
            'billing_city'       => $params['billing_city'] ?? 'Istanbul',
            'billing_country'    => $params['billing_country'] ?? 'TR',
            'billing_postcode'   => $params['billing_postcode'] ?? '34000',
            'shipping_address'   => $params['billing_address'] ?? 'Türkiye',
            'shipping_city'      => $params['billing_city'] ?? 'Istanbul',
            'shipping_country'   => $params['billing_country'] ?? 'TR',
            'shipping_postcode'  => $params['billing_postcode'] ?? '34000',
            'total_order_value'  => $params['amount'],
            'currency'           => $this->getCurrencyCode($params['currency'] ?? 'TRY'),
            'platform'           => 0,
            'is_in_frame'        => 0,
            'current_language'   => 0, // 0 = TR
            'modul_version'      => $this->moduleVersion,
            'random_nr'          => $randomNr,
        ];

        $signData = $args['random_nr'] . $args['platform_order_id'] . $args['total_order_value'] . $args['currency'];
        $signature = base64_encode(hash_hmac('sha256', $signData, $this->apiSecret, true));

        $args['signature'] = $signature;
        $args['callback'] = $params['callback_url'];

        $formHtml = '<html><head><meta charset="UTF-8"></head><body>';
        $formHtml .= '<form id="shopier_payment_form" method="POST" action="' . $this->paymentUrl . '">';
        foreach ($args as $key => $value) {
            $formHtml .= '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">';
        }
        $formHtml .= '</form>';
        $formHtml .= '<script>document.getElementById("shopier_payment_form").submit();</script>';
        $formHtml .= '</body></html>';

        return $formHtml;
    }

    public function verifyCallback(array $postData): bool
    {
        if (!isset($postData['random_nr'], $postData['platform_order_id'], $postData['total_order_value'], $postData['currency'], $postData['signature'])) {
            return false;
        }

        $signData = $postData['random_nr'] . $postData['platform_order_id'] . $postData['total_order_value'] . $postData['currency'];
        $expectedSignature = base64_encode(hash_hmac('sha256', $signData, $this->apiSecret, true));

        return hash_equals($expectedSignature, $postData['signature']);
    }

    protected function getCurrencyCode(string $currency): int
    {
        return match (strtoupper($currency)) {
            'TRY', 'TL' => 0,
            'USD' => 1,
            'EUR' => 2,
            default => 0,
        };
    }
}
