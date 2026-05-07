<?php

namespace App\Services;

use App\Models\PaytrTransaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * PayTR ödeme entegrasyonu — Iframe API
 * https://dev.paytr.com/iframe-api
 *
 * - Iframe API kullanır (kart bilgileri PayTR'da, PCI-safe)
 * - Test mode toggle (config'den)
 * - Tüm istek/cevaplar paytr_transactions tablosuna loglanır
 * - Callback hash doğrulaması idempotent
 */
class PaytrService
{
    public const IFRAME_API_URL = 'https://www.paytr.com/odeme/api/get-token';
    public const IFRAME_EMBED_URL = 'https://www.paytr.com/odeme/guvenli/';
    public const EFT_API_URL = 'https://www.paytr.com/odeme/api/get-token';
    public const REFUND_API_URL = 'https://www.paytr.com/odeme/iade';

    protected string $merchantId;
    protected string $merchantKey;
    protected string $merchantSalt;
    protected bool $testMode;
    protected string $okUrl;
    protected string $failUrl;
    protected string $callbackUrl;
    protected int $timeout;

    public function __construct(?string $okUrl = null, ?string $failUrl = null)
    {
        $this->merchantId   = (string) config('paytr.credentials.merchant_id', '');
        $this->merchantKey  = (string) config('paytr.credentials.merchant_key', '');
        $this->merchantSalt = (string) config('paytr.credentials.merchant_salt', '');
        $this->testMode     = (bool)   config('paytr.options.test_mode', true);
        $this->okUrl        = $okUrl ?? ((string) config('paytr.options.success_url') ?: route('portal.paytr.paymentResult', ['result' => 'success']));
        $this->failUrl      = $failUrl ?? ((string) config('paytr.options.fail_url')    ?: route('portal.paytr.paymentResult', ['result' => 'fail']));
        $this->callbackUrl  = (string) config('paytr.options.callback_url') ?: url('/callback-paytr');
        $this->timeout      = (int)    config('paytr.options.timeout', 60);
    }

    /**
     * Iframe API: Token al, iframe URL'i döndür
     *
     * @param  array  $checkout  ['merchant_oid', 'email', 'amount' (kuruş), 'user_name', 'user_address', 'user_phone', 'user_basket' (array)]
     * @return array  ['success' => bool, 'token' => string|null, 'iframe_url' => string|null, 'reason' => string|null, 'raw' => array]
     */
    public function getIframeToken(array $checkout): array
    {
        $required = ['merchant_oid', 'email', 'amount', 'user_name', 'user_address', 'user_phone', 'user_basket'];
        foreach ($required as $field) {
            if (!isset($checkout[$field])) {
                return ['success' => false, 'token' => null, 'iframe_url' => null, 'reason' => "missing_field:{$field}", 'raw' => []];
            }
        }

        if (!$this->isConfigured()) {
            return ['success' => false, 'token' => null, 'iframe_url' => null, 'reason' => 'paytr_not_configured', 'raw' => []];
        }

        $userIp        = request()->ip() ?: '127.0.0.1';
        $merchantOid   = (string) $checkout['merchant_oid'];
        $email         = (string) $checkout['email'];
        $paymentAmount = (int) $checkout['amount']; // kuruş
        $currency      = $checkout['currency'] ?? 'TL';
        $testMode      = $this->testMode ? 1 : 0;
        $noInstallment = isset($checkout['no_installment']) ? (int) $checkout['no_installment'] : 0;
        $maxInstallment = isset($checkout['max_installment']) ? (int) $checkout['max_installment'] : 0;
        $userBasket    = base64_encode(json_encode($checkout['user_basket'], JSON_UNESCAPED_UNICODE));
        $debugOn       = (int) config('paytr.options.debug_on', 1);
        $clientLang    = $checkout['lang'] ?? 'tr';
        $timeoutLimit  = (int) config('paytr.options.iframe_timeout_min', 30);

        $hashStr = $this->merchantId . $userIp . $merchantOid . $email . $paymentAmount . $userBasket . $noInstallment . $maxInstallment . $currency . $testMode;
        $paytrToken = base64_encode(hash_hmac('sha256', $hashStr . $this->merchantSalt, $this->merchantKey, true));

        $postVals = [
            'merchant_id'      => $this->merchantId,
            'user_ip'          => $userIp,
            'merchant_oid'     => $merchantOid,
            'email'            => $email,
            'payment_amount'   => $paymentAmount,
            'paytr_token'      => $paytrToken,
            'user_basket'      => $userBasket,
            'debug_on'         => $debugOn,
            'no_installment'   => $noInstallment,
            'max_installment'  => $maxInstallment,
            'user_name'        => $checkout['user_name'],
            'user_address'     => $checkout['user_address'],
            'user_phone'       => $checkout['user_phone'],
            'merchant_ok_url'  => $this->okUrl,
            'merchant_fail_url'=> $this->failUrl,
            'timeout_limit'    => $timeoutLimit,
            'currency'         => $currency,
            'test_mode'        => $testMode,
            'lang'             => $clientLang,
        ];

        // Persist transaction log (request)
        $transaction = $this->logTransaction([
            'merchant_oid' => $merchantOid,
            'type'         => 'iframe_token_request',
            'amount'       => $paymentAmount / 100,
            'currency'     => $currency,
            'test_mode'    => $this->testMode,
            'request_payload' => $postVals,
        ]);

        try {
            $response = Http::asForm()
                ->timeout($this->timeout)
                ->post(self::IFRAME_API_URL, $postVals);
        } catch (\Throwable $e) {
            Log::error('PAYTR_HTTP_FAIL', ['error' => $e->getMessage(), 'oid' => $merchantOid]);
            $this->updateTransaction($transaction, ['status' => 'http_error', 'response_payload' => ['error' => $e->getMessage()]]);
            return ['success' => false, 'token' => null, 'iframe_url' => null, 'reason' => 'http_error: ' . $e->getMessage(), 'raw' => []];
        }

        $body = $response->json();
        $this->updateTransaction($transaction, [
            'status' => ($body['status'] ?? '') === 'success' ? 'token_issued' : 'token_failed',
            'response_payload' => $body,
        ]);

        if (!$response->successful() || ($body['status'] ?? null) !== 'success') {
            $reason = $body['reason'] ?? ('http_' . $response->status());
            return ['success' => false, 'token' => null, 'iframe_url' => null, 'reason' => $reason, 'raw' => $body ?? []];
        }

        $token = $body['token'];
        return [
            'success'    => true,
            'token'      => $token,
            'iframe_url' => self::IFRAME_EMBED_URL . $token,
            'reason'     => null,
            'raw'        => $body,
        ];
    }

    /**
     * Callback hash doğrulama
     * https://dev.paytr.com/bildirim-url-i
     */
    public function verifyCallback(array $post): bool
    {
        if (!isset($post['merchant_oid'], $post['status'], $post['total_amount'], $post['hash'])) {
            return false;
        }
        $expected = base64_encode(hash_hmac(
            'sha256',
            $post['merchant_oid'] . $this->merchantSalt . $post['status'] . $post['total_amount'],
            $this->merchantKey,
            true
        ));
        return hash_equals($expected, (string) $post['hash']);
    }

    /**
     * Test connection: token API'sini sahte küçük bir istekle dener.
     */
    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'reason' => 'PayTR bilgileri eksik. Merchant ID/Key/Salt giriniz.'];
        }

        // Mini test: sadece merchant_id ile bir token isteği — başarısız bile olsa "merchant_id geçersiz" gibi cevap geliyorsa bağlantı tamam.
        $userIp = request()->ip() ?: '127.0.0.1';
        $merchantOid = 'test' . time();
        $email = 'test@test.com';
        $paymentAmount = 100; // 1 TL
        $testMode = 1;
        $noInstallment = 0;
        $maxInstallment = 0;
        $currency = 'TL';
        $userBasket = base64_encode(json_encode([['Test', '1.00', 1]]));

        $hashStr = $this->merchantId . $userIp . $merchantOid . $email . $paymentAmount . $userBasket . $noInstallment . $maxInstallment . $currency . $testMode;
        $paytrToken = base64_encode(hash_hmac('sha256', $hashStr . $this->merchantSalt, $this->merchantKey, true));

        $postVals = [
            'merchant_id'      => $this->merchantId,
            'user_ip'          => $userIp,
            'merchant_oid'     => $merchantOid,
            'email'            => $email,
            'payment_amount'   => $paymentAmount,
            'paytr_token'      => $paytrToken,
            'user_basket'      => $userBasket,
            'debug_on'         => 1,
            'no_installment'   => $noInstallment,
            'max_installment'  => $maxInstallment,
            'user_name'        => 'Test User',
            'user_address'     => 'Test Address',
            'user_phone'       => '5000000000',
            'merchant_ok_url'  => $this->okUrl,
            'merchant_fail_url'=> $this->failUrl,
            'timeout_limit'    => 30,
            'currency'         => $currency,
            'test_mode'        => $testMode,
            'lang'             => 'tr',
        ];

        try {
            $response = Http::asForm()->timeout(15)->post(self::IFRAME_API_URL, $postVals);
        } catch (\Throwable $e) {
            return ['success' => false, 'reason' => 'HTTP hatası: ' . $e->getMessage()];
        }

        $body = $response->json();
        if (($body['status'] ?? null) === 'success') {
            return ['success' => true, 'reason' => 'Bağlantı başarılı. Token alındı.', 'token' => $body['token'] ?? null];
        }

        // Kullanıcıya anlamlı geri bildirim
        $reason = $body['reason'] ?? ('HTTP ' . $response->status());
        return ['success' => false, 'reason' => 'PayTR cevap verdi: ' . $reason];
    }

    public function isConfigured(): bool
    {
        return $this->merchantId !== '' && $this->merchantKey !== '' && $this->merchantSalt !== '';
    }

    public function isTestMode(): bool
    {
        return $this->testMode;
    }

    public function getIframeEmbedUrl(string $token): string
    {
        return self::IFRAME_EMBED_URL . $token;
    }

    public function logTransaction(array $data): ?PaytrTransaction
    {
        try {
            return PaytrTransaction::create(array_merge([
                'status'         => 'pending',
                'reference_uuid' => (string) Str::uuid(),
            ], $data));
        } catch (\Throwable $e) {
            Log::error('PAYTR_TX_LOG_FAIL', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function updateTransaction(?PaytrTransaction $tx, array $data): void
    {
        if (!$tx) return;
        try {
            $tx->update($data);
        } catch (\Throwable $e) {
            Log::error('PAYTR_TX_UPDATE_FAIL', ['error' => $e->getMessage(), 'tx' => $tx->id ?? null]);
        }
    }

    /**
     * EFT/Havale token (mevcut özellik korundu)
     */
    public function getEftIframeToken(string $merchantOid, string $email, int $paymentAmount, ?string $userName = null, ?string $userPhone = null): string
    {
        $userIp = request()->ip() ?: '127.0.0.1';
        $paymentType = 'eft';
        $testMode = (string) ($this->testMode ? 1 : 0);

        $hashStr = $this->merchantId . $userIp . $merchantOid . $email . $paymentAmount . $paymentType . $testMode;
        $paytrToken = base64_encode(hash_hmac('sha256', $hashStr . $this->merchantSalt, $this->merchantKey, true));

        $postVals = [
            'merchant_id'    => $this->merchantId,
            'user_ip'        => $userIp,
            'merchant_oid'   => $merchantOid,
            'email'          => $email,
            'payment_amount' => $paymentAmount,
            'payment_type'   => $paymentType,
            'paytr_token'    => $paytrToken,
            'debug_on'       => 1,
            'timeout_limit'  => 30,
            'test_mode'      => $testMode,
        ];
        if ($userName)  $postVals['user_name']  = $userName;
        if ($userPhone) $postVals['user_phone'] = $userPhone;

        $response = Http::asForm()->timeout($this->timeout)->post(self::EFT_API_URL, $postVals);
        if (!$response->successful()) {
            throw new \Exception('PayTR EFT API bağlantı hatası: ' . $response->status());
        }
        $result = $response->json();
        if (($result['status'] ?? null) !== 'success') {
            throw new \Exception('PayTR EFT token hatası: ' . ($result['reason'] ?? 'Bilinmeyen hata'));
        }
        return $result['token'];
    }

    /**
     * Refund (iade) - PayTR refund API
     */
    public function refund(string $merchantOid, int $returnAmount, string $reference = ''): array
    {
        $hashStr = $this->merchantId . $merchantOid . $returnAmount . $this->merchantSalt;
        $paytrToken = base64_encode(hash_hmac('sha256', $hashStr, $this->merchantKey, true));

        $postVals = [
            'merchant_id'   => $this->merchantId,
            'merchant_oid'  => $merchantOid,
            'return_amount' => $returnAmount, // kuruş
            'paytr_token'   => $paytrToken,
        ];
        if ($reference) {
            $postVals['reference_no'] = $reference;
        }

        $tx = $this->logTransaction([
            'merchant_oid' => $merchantOid,
            'type'         => 'refund_request',
            'amount'       => $returnAmount / 100,
            'currency'     => 'TL',
            'test_mode'    => $this->testMode,
            'request_payload' => $postVals,
        ]);

        try {
            $response = Http::asForm()->timeout($this->timeout)->post(self::REFUND_API_URL, $postVals);
        } catch (\Throwable $e) {
            $this->updateTransaction($tx, ['status' => 'http_error', 'response_payload' => ['error' => $e->getMessage()]]);
            return ['success' => false, 'reason' => 'http_error: ' . $e->getMessage()];
        }

        $body = $response->json();
        $ok = ($body['status'] ?? null) === 'success';
        $this->updateTransaction($tx, ['status' => $ok ? 'refunded' : 'refund_failed', 'response_payload' => $body]);

        return ['success' => $ok, 'reason' => $body['err_msg'] ?? ($body['reason'] ?? null), 'raw' => $body];
    }
    /**
     * @deprecated Use getIframeToken() + iframe.blade view instead.
     * Backwards-compat shim for existing CheckoutController / PublicInvoiceController.
     * Returns an HTML chunk that auto-redirects user to PayTR iframe page.
     */
    public function sendData(array $data, $price, array $basket): string
    {
        $merchantOid = (string) ($data['checkout_id'] ?? ('OID' . time()));
        $email = (string) ($data['email'] ?? 'noreply@example.com');
        $userName = (string) ($data['name'] ?? ($data['cc_owner'] ?? 'Customer'));
        $userPhone = preg_replace('/[^0-9]/', '', (string) ($data['phone'] ?? '5000000000'));
        $userAddress = (string) ($data['address'] ?? '-');

        $userBasket = [];
        foreach ($basket as $row) {
            if (is_array($row) && count($row) >= 2) {
                $userBasket[] = [(string) $row[0], number_format((float) $row[1], 2, '.', ''), (int) ($row[2] ?? 1)];
            }
        }

        $amountKurus = (int) round(((float) $price) * 100);

        $result = $this->getIframeToken([
            'merchant_oid' => $merchantOid,
            'email'        => $email,
            'amount'       => $amountKurus,
            'user_name'    => $userName,
            'user_address' => $userAddress,
            'user_phone'   => $userPhone,
            'user_basket'  => $userBasket,
            'currency'     => 'TL',
            'lang'         => app()->getLocale() === 'en' ? 'en' : 'tr',
        ]);

        if (!$result['success']) {
            return '<div class="alert alert-danger">PayTR ödeme başlatılamadı: ' . htmlspecialchars($result['reason'] ?? 'unknown') . '</div>';
        }

        $iframeUrl = $result['iframe_url'];
        $testBanner = '';
        if ($this->isTestMode()) {
            $testBanner = '<div style="padding:12px 16px;background:linear-gradient(90deg,#f59e0b,#ef4444);color:#fff;font-weight:600;border-radius:10px;margin-bottom:14px;">🧪 TEST MODU AKTİF — gerçek ödeme alınmaz</div>';
        }

        return $testBanner . '<iframe src="' . htmlspecialchars($iframeUrl) . '" style="width:100%;min-height:560px;border:0;" frameborder="0" scrolling="auto"></iframe>'
             . '<script src="https://www.paytr.com/js/iframeResizer.min.js"></script>'
             . '<script>if(typeof iFrameResize!=="undefined"){iFrameResize({},"iframe");}</script>';
    }

}
