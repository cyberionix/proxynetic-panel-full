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
        $this->gatewayUrl = config('nestpay.gateway_url', 'https://entegrasyon.asseco-see.com.tr/fim/est3dgate');
    }

    public function generateFormHtml(array $cardData, $amount, $orderId, $okUrl, $failUrl, $installment = 0)
    {
        $rnd = microtime();
        $taksit = ($installment > 1) ? (string)$installment : '';
        $islemtipi = 'Auth';
        $formattedAmount = number_format((float)$amount, 2, '.', '');

        $hashStr = $this->clientId . $orderId . $formattedAmount . $okUrl . $failUrl . $islemtipi . $taksit . $rnd . $this->storeKey;
        $hash = base64_encode(sha1($hashStr, true));

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
        ]);

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
            'hash'                            => $hash,
            'lang'                            => 'tr',
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
        if (!empty($cardData['phone'])) {
            $params['tel'] = $cardData['phone'];
        }

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
        $hashParamsVal = $request->input('HASHPARAMSVAL', '');
        $responseHash = $request->input('HASH', '');

        if (empty($responseHash)) {
            return false;
        }

        $hashStr = $hashParamsVal . $this->storeKey;
        $calculatedHash = base64_encode(sha1($hashStr, true));

        return hash_equals($calculatedHash, $responseHash);
    }

    public function isPaymentSuccessful(Request $request)
    {
        $mdStatus = $request->input('mdStatus', '');
        $procReturnCode = $request->input('ProcReturnCode', '');

        return in_array($mdStatus, ['1', '2', '3', '4']) && $procReturnCode === '00';
    }
}
