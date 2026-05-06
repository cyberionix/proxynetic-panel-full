<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NestpayService
{
    protected $clientId;
    protected $storeKey;
    protected $gatewayUrl;

    public function __construct()
    {
        $this->clientId = config('nestpay.client_id', '');
        $this->storeKey = config('nestpay.store_key', '');
        $this->gatewayUrl = config('nestpay.gateway_url', 'https://sanalpos.isbank.com.tr/fim/est3Dgate');
    }

    private function calculateHashVer3(array $params, $storeKey)
    {
        $paramNames = array_keys($params);
        natcasesort($paramNames);

        $hashval = '';
        foreach ($paramNames as $param) {
            $paramValue = (string)$params[$param];
            $escapedValue = str_replace('|', '\\|', str_replace('\\', '\\\\', $paramValue));
            $lowerParam = strtolower($param);
            if ($lowerParam !== 'hash' && $lowerParam !== 'encoding') {
                $hashval .= $escapedValue . '|';
            }
        }

        $escapedStoreKey = str_replace('|', '\\|', str_replace('\\', '\\\\', $storeKey));
        $hashval .= $escapedStoreKey;

        $calculatedHashValue = hash('sha512', $hashval);
        $hash = base64_encode(pack('H*', $calculatedHashValue));

        return $hash;
    }

    public function generateFormHtml(array $cardData, $amount, $orderId, $okUrl, $failUrl, $installment = 0)
    {
        $rnd = microtime();
        $taksit = ($installment > 1) ? (string)$installment : '';
        $islemtipi = 'Auth';
        $formattedAmount = number_format((float)$amount, 2, '.', '');

        $params = [
            'clientid'                        => $this->clientId,
            'storetype'                       => '3D_PAY',
            'amount'                          => $formattedAmount,
            'currency'                        => '949',
            'oid'                             => (string)$orderId,
            'okUrl'                           => $okUrl,
            'failUrl'                         => $failUrl,
            'TranType'                        => $islemtipi,
            'Instalment'                      => $taksit,
            'rnd'                             => $rnd,
            'lang'                            => 'tr',
            'hashAlgorithm'                   => 'ver3',
            'pan'                             => $cardData['card_number'],
            'Ecom_Payment_Card_ExpDate_Month' => str_pad($cardData['expiry_month'], 2, '0', STR_PAD_LEFT),
            'Ecom_Payment_Card_ExpDate_Year'  => str_pad($cardData['expiry_year'], 2, '0', STR_PAD_LEFT),
            'cv2'                             => $cardData['cvv'],
        ];

        if (!empty($cardData['card_name'])) {
            $params['firmaadi'] = $cardData['card_name'];
        }
        if (!empty($cardData['email'])) {
            $params['email'] = $cardData['email'];
        }

        $hash = $this->calculateHashVer3($params, $this->storeKey);
        $params['HASH'] = $hash;

        Log::info('NESTPAY_HASH_DEBUG', [
            'clientId' => $this->clientId,
            'oid' => $orderId,
            'amount' => $formattedAmount,
            'okUrl' => $okUrl,
            'failUrl' => $failUrl,
            'TranType' => $islemtipi,
            'Instalment' => $taksit,
            'rnd' => $rnd,
            'hash' => $hash,
            'hashAlgorithm' => 'ver3',
            'gatewayUrl' => $this->gatewayUrl,
        ]);

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
        $retrievedHash = $request->input('HASH', '');
        if (empty($retrievedHash)) {
            return false;
        }

        $postParams = $request->all();
        $paramNames = array_keys($postParams);
        natcasesort($paramNames);

        $hashval = '';
        foreach ($paramNames as $param) {
            $paramValue = (string)($postParams[$param] ?? '');
            $escapedValue = str_replace('|', '\\|', str_replace('\\', '\\\\', $paramValue));
            $lowerParam = strtolower($param);
            if ($lowerParam !== 'hash' && $lowerParam !== 'encoding' && $lowerParam !== 'countdown') {
                $hashval .= $escapedValue . '|';
            }
        }

        $escapedStoreKey = str_replace('|', '\\|', str_replace('\\', '\\\\', $this->storeKey));
        $hashval .= $escapedStoreKey;

        $calculatedHashValue = hash('sha512', $hashval);
        $actualHash = base64_encode(pack('H*', $calculatedHashValue));

        return hash_equals($actualHash, $retrievedHash);
    }

    public function isPaymentSuccessful(Request $request)
    {
        $mdStatus = $request->input('mdStatus', '');
        $procReturnCode = $request->input('ProcReturnCode', '');

        return in_array($mdStatus, ['1', '2', '3', '4']) && $procReturnCode === '00';
    }
}
