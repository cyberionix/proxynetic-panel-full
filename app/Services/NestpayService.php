<?php

namespace App\Services;

use Illuminate\Http\Request;

class NestpayService
{
    protected $clientId;
    protected $storeKey;
    protected $gatewayUrl;

    public function __construct()
    {
        $this->clientId = config('nestpay.client_id', '');
        $this->storeKey = config('nestpay.store_key', '');
        $this->gatewayUrl = config('nestpay.gateway_url', 'https://entegrasyon.asseco-see.com.tr/fim/est3dgate');
    }

    protected function escapeValue($value)
    {
        $value = str_replace('\\', '\\\\', (string)$value);
        $value = str_replace('|', '\\|', $value);
        return $value;
    }

    protected function calculateHash(array $params)
    {
        ksort($params, SORT_STRING);

        $escaped = [];
        foreach ($params as $value) {
            $escaped[] = $this->escapeValue($value);
        }

        $hashStr = implode('|', $escaped) . '|' . $this->escapeValue($this->storeKey);

        return base64_encode(hash('sha512', $hashStr, true));
    }

    public function generateFormHtml(array $cardData, $amount, $orderId, $okUrl, $failUrl, $installment = 0)
    {
        $rnd = microtime(true) . mt_rand(100000, 999999);

        $expiryYear = $cardData['expiry_year'];
        if (strlen($expiryYear) == 2) {
            $expiryYear = '20' . $expiryYear;
        }

        $params = [
            'clientid'                        => $this->clientId,
            'storetype'                       => '3d_pay',
            'amount'                          => number_format((float)$amount, 2, '.', ''),
            'currency'                        => '949',
            'oid'                             => (string)$orderId,
            'okUrl'                           => $okUrl,
            'failUrl'                         => $failUrl,
            'TranType'                        => 'Auth',
            'Instalment'                      => ($installment > 1) ? (string)$installment : '',
            'rnd'                             => $rnd,
            'lang'                            => 'tr',
            'hashAlgorithm'                   => 'ver3',
            'pan'                             => $cardData['card_number'],
            'Ecom_Payment_Card_ExpDate_Month' => str_pad($cardData['expiry_month'], 2, '0', STR_PAD_LEFT),
            'Ecom_Payment_Card_ExpDate_Year'  => $expiryYear,
            'cv2'                             => $cardData['cvv'],
        ];

        if (!empty($cardData['card_name'])) {
            $params['BillToName'] = $cardData['card_name'];
        }
        if (!empty($cardData['email'])) {
            $params['email'] = $cardData['email'];
        }
        if (!empty($cardData['phone'])) {
            $params['tel'] = $cardData['phone'];
        }

        $hash = $this->calculateHash($params);
        $params['hash'] = $hash;

        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>3D Secure Yönlendirme</title></head><body>';
        $html .= '<div style="text-align:center;margin-top:100px;"><p>3D Secure doğrulama sayfasına yönlendiriliyorsunuz...</p>';
        $html .= '<p><small>Otomatik yönlendirilmezseniz aşağıdaki butona tıklayın.</small></p></div>';
        $html .= '<form id="nestpayForm" method="POST" action="' . htmlspecialchars($this->gatewayUrl) . '">';
        foreach ($params as $key => $value) {
            $html .= '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">';
        }
        $html .= '<div style="text-align:center;"><button type="submit" style="padding:10px 30px;font-size:16px;cursor:pointer;">Devam Et</button></div>';
        $html .= '</form>';
        $html .= '<script>document.getElementById("nestpayForm").submit();</script>';
        $html .= '</body></html>';

        return $html;
    }

    public function verifyCallbackHash(Request $request)
    {
        $hashParams = $request->input('HASHPARAMS', '');
        $responseHash = $request->input('HASH', '');

        if (empty($hashParams) || empty($responseHash)) {
            return false;
        }

        $paramsArray = explode(':', $hashParams);
        $values = [];
        foreach ($paramsArray as $paramName) {
            $paramName = trim($paramName);
            if ($paramName !== '') {
                $values[] = $this->escapeValue($request->input($paramName, ''));
            }
        }

        $hashStr = implode('|', $values) . '|' . $this->escapeValue($this->storeKey);
        $calculatedHash = base64_encode(hash('sha512', $hashStr, true));

        return hash_equals($calculatedHash, $responseHash);
    }

    public function isPaymentSuccessful(Request $request)
    {
        $mdStatus = $request->input('mdStatus', '');
        $procReturnCode = $request->input('ProcReturnCode', '');

        return in_array($mdStatus, ['1', '2', '3', '4']) && $procReturnCode === '00';
    }
}
