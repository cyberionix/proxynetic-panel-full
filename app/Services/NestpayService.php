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
        $this->gatewayUrl = config('nestpay.gateway_url', 'https://istest.asseco-see.com.tr/fim/est3Dgate');
    }

    private function escapeHashValue($value)
    {
        $value = str_replace('\\', '\\\\', $value);
        $value = str_replace('|', '\\|', $value);
        return $value;
    }

    private function calculateHashVer3(array $params, $storeKey)
    {
        $hashParams = $params;
        unset($hashParams['hash'], $hashParams['encoding'], $hashParams['hashAlgorithm']);

        ksort($hashParams);

        $hashParts = [];
        foreach ($hashParams as $value) {
            $hashParts[] = $this->escapeHashValue((string)$value);
        }

        $hashString = implode('|', $hashParts) . '|' . $this->escapeHashValue($storeKey);
        $hash = base64_encode(hash('sha512', $hashString, true));

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
            'storetype'                       => '3d_pay',
            'amount'                          => $formattedAmount,
            'currency'                        => '949',
            'oid'                             => (string)$orderId,
            'okUrl'                           => $okUrl,
            'failUrl'                         => $failUrl,
            'islemtipi'                       => $islemtipi,
            'taksit'                          => $taksit,
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
        $params['hash'] = $hash;

        Log::info('NESTPAY_HASH_DEBUG', [
            'clientId' => $this->clientId,
            'oid' => $orderId,
            'amount' => $formattedAmount,
            'okUrl' => $okUrl,
            'failUrl' => $failUrl,
            'islemtipi' => $islemtipi,
            'taksit' => $taksit,
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
        $responseHash = $request->input('HASH', '');
        if (empty($responseHash)) {
            return false;
        }

        $hashAlgorithm = $request->input('HASHPARAMSVAL') !== null ? 'sha1' : 'auto';
        $hashParamsVal = $request->input('HASHPARAMSVAL', '');
        $hashParams = $request->input('HASHPARAMS', '');

        if (!empty($hashParamsVal)) {
            $hashStr = $hashParamsVal . $this->storeKey;

            $calculatedSha1 = base64_encode(sha1($hashStr, true));
            if (hash_equals($calculatedSha1, $responseHash)) {
                return true;
            }

            $calculatedSha512 = base64_encode(hash('sha512', $hashStr, true));
            if (hash_equals($calculatedSha512, $responseHash)) {
                return true;
            }
        }

        if (!empty($hashParams)) {
            $checkStr = '';
            foreach (explode(':', $hashParams) as $p) {
                $p = trim($p);
                if ($p !== '') {
                    $checkStr .= $request->input($p, '');
                }
            }
            $checkStr .= $this->storeKey;

            $calculatedSha1 = base64_encode(sha1($checkStr, true));
            if (hash_equals($calculatedSha1, $responseHash)) {
                return true;
            }

            $calculatedSha512 = base64_encode(hash('sha512', $checkStr, true));
            if (hash_equals($calculatedSha512, $responseHash)) {
                return true;
            }
        }

        return false;
    }

    public function isPaymentSuccessful(Request $request)
    {
        $mdStatus = $request->input('mdStatus', '');
        $procReturnCode = $request->input('ProcReturnCode', '');

        return in_array($mdStatus, ['1', '2', '3', '4']) && $procReturnCode === '00';
    }
}
