<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Checkout;
use App\Models\Invoice;
use App\Services\PaytrService;
use App\Traits\AjaxResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Ramsey\Uuid\Uuid;

/**
 * PayTR iframe API ödeme akışı.
 * - POST /portal/paytr/initiate     -> Iframe token üretir, iframe URL'i döndürür
 * - GET  /portal/paytr/iframe/{id}  -> Iframe sayfası
 * - GET  /portal/paytr/result       -> PayTR sonrası kullanıcı buraya döner
 * - POST /callback-paytr            -> PayTR sunucusundan callback (web.php'de)
 */
class PaytrController extends Controller
{
    use AjaxResponses;

    /**
     * Adım 1: Iframe için token al, iframe URL döndür.
     * Body: { invoice_id?, invoice_address_id }
     */
    public function initiate(Request $request, PaytrService $paytr)
    {
        if (!$paytr->isConfigured()) {
            return $this->errorResponse('PayTR henüz yapılandırılmadı.');
        }

        if (Auth::user()->security && Auth::user()->security->is_limit_payment_methods == 1
            && !in_array("CREDIT_CARD", Auth::user()->security->payment_methods ?? [])) {
            return $this->errorResponse("Kredi/Banka kartı ile ödeme yapamazsınız. Lütfen diğer ödeme yöntemleri ile devam ediniz.");
        }

        $request->validate([
            'invoice_id' => 'nullable|integer',
            'invoice_address_id' => [
                'required',
                Rule::exists('user_addresses', 'id')->where(fn($q) => $q->where('user_id', Auth::id())),
            ],
        ], [
            'invoice_address_id.required' => __('custom_field_is_required', ['name' => __('invoice_address')]),
            'invoice_address_id.exists' => __('please_choose_a_valid_address'),
        ]);

        DB::beginTransaction();
        try {
            $invoiceAddress = method_exists($this, 'getUserInvoiceAddress')
                ? $this->getUserInvoiceAddress($request->invoice_address_id)
                : $this->resolveAddress($request->invoice_address_id);

            if (!$invoiceAddress) {
                return $this->errorResponse(__('please_choose_a_valid_address'));
            }

            $netToken = Uuid::uuid4()->toString();
            $basket = [];
            $totalPriceWithVat = 0;

            if ($request->invoice_id) {
                $invoice = Invoice::where(['id' => $request->invoice_id, 'user_id' => Auth::id()])->first();
                if (!$invoice) {
                    return $this->errorResponse(__('invoice_not_found'));
                }
                $invoice->update(['invoice_address' => $invoiceAddress]);
                $totalPriceWithVat = $invoice->total_price_with_vat;

                foreach ($invoice->items as $item) {
                    $basket[] = [$item->name, number_format($item->total_price_with_vat, 2, '.', ''), 1];
                }

                $checkout = Checkout::create([
                    'type'        => 'CREDIT_CARD',
                    'status'      => '3DS_REDIRECTED',
                    'amount'      => $totalPriceWithVat,
                    'uuid_value'  => $netToken,
                    'invoice_id'  => $invoice->id,
                    'user_id'     => Auth::id(),
                    'channel'     => 'PAYTR',
                    'test_mode'   => $paytr->isTestMode(),
                    'extra_params'=> ['invoice_address_data' => $invoiceAddress],
                ]);
            } else {
                $userBasket = Auth::user()->basket;
                if (!$userBasket || count($userBasket->items) <= 0) {
                    return $this->errorResponse(__('you_must_add_at_least_one_item_to_your_cart'));
                }

                $basketPricesData = [];
                foreach ($userBasket->items as $item) {
                    $servicePrice = 0;
                    $additionalServices = [];
                    if ($item->additional_services) {
                        foreach ($item->additional_services as $key => $service) {
                            $serviceData = $item->getAdditionalServices($key, $service);
                            $additionalServices[] = $serviceData;
                            $servicePrice += $serviceData['price_without_vat'];
                        }
                    }
                    $priceData = $item->price->toArray();
                    $priceData['price'] = $item->price->price_without_vat + $servicePrice;
                    $priceData['total_vat'] = ($priceData['price'] * $item->product->vat_percent) / 100;
                    $priceData['price_with_vat'] = $priceData['price'] + $priceData['total_vat'];
                    $totalPriceWithVat += $priceData['price_with_vat'];
                    $priceData['product']['additional_services'] = $additionalServices;
                    $basketPricesData[] = $priceData;
                    $basket[] = [$item->product->name, number_format($priceData['price_with_vat'], 2, '.', ''), 1];
                }

                $summary = $userBasket->basketSummary();
                $checkout_amount = $summary['real_total'] ?? $totalPriceWithVat;

                $checkout = Checkout::create([
                    'type'       => 'CREDIT_CARD',
                    'status'     => '3DS_REDIRECTED',
                    'amount'     => $checkout_amount,
                    'uuid_value' => $netToken,
                    'channel'    => 'PAYTR',
                    'user_id'    => Auth::id(),
                    'basket_id'  => $userBasket->id,
                    'test_mode'  => $paytr->isTestMode(),
                    'extra_params' => [
                        'price_data' => $basketPricesData,
                        'invoice_address_data' => $invoiceAddress,
                    ],
                ]);
                $totalPriceWithVat = $checkout_amount;
            }

            // PayTR merchant_oid'i tahmin edilmesin diye id+offset (mevcut callback ile uyumlu)
            $merchantOid = (string) ($checkout->id + 51);

            $address = trim(
                ($invoiceAddress['address'] ?? '') . ' ' .
                ($invoiceAddress['city']['title'] ?? '') . '/' .
                ($invoiceAddress['district']['title'] ?? '') . ' - ' .
                ($invoiceAddress['country']['title'] ?? '')
            );

            $tokenResult = $paytr->getIframeToken([
                'merchant_oid' => $merchantOid,
                'email'        => Auth::user()->email,
                'amount'       => (int) round($totalPriceWithVat * 100), // kuruş
                'user_name'    => Auth::user()->full_name ?: Auth::user()->name ?: 'Müşteri',
                'user_address' => $address ?: '-',
                'user_phone'   => Auth::user()->phone ?: '5000000000',
                'user_basket'  => $basket,
                'currency'     => 'TL',
                'lang'         => app()->getLocale() === 'en' ? 'en' : 'tr',
            ]);

            if (!$tokenResult['success']) {
                $checkout->update(['status' => 'FAILED']);
                DB::commit();
                Log::error('PAYTR_TOKEN_FAIL', ['oid' => $merchantOid, 'reason' => $tokenResult['reason']]);
                if (!$request->wantsJson() && !$request->ajax()) {
                    return redirect()->route('portal.dashboard')
                        ->with('payment_result_status', 'error')
                        ->with('payment_result_message', 'Ödeme başlatılamadı: ' . $tokenResult['reason']);
                }
                return $this->errorResponse('Ödeme başlatılamadı: ' . $tokenResult['reason']);
            }

            $checkout->update(['paytr_token' => $tokenResult['token']]);
            DB::commit();

            // Non-AJAX form submit: redirect to iframe page (browser-friendly UX)
            if (!$request->wantsJson() && !$request->ajax()) {
                return redirect()->route('portal.paytr.iframe', ['checkout' => $checkout->id]);
            }

            return $this->successResponse('OK', [
                'iframe_url'  => $tokenResult['iframe_url'],
                'iframe_html' => view('portal.paytr.iframe', [
                    'iframeUrl' => $tokenResult['iframe_url'],
                    'token'     => $tokenResult['token'],
                    'testMode'  => $paytr->isTestMode(),
                ])->render(),
                'test_mode'   => $paytr->isTestMode(),
                'test_cards'  => $paytr->isTestMode() ? config('paytr.test_cards', []) : [],
                'checkout_id' => $checkout->id,
                'redirect_url'=> route('portal.paytr.iframe', ['checkout' => $checkout->id]),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('PAYTR_INITIATE_FAIL', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return $this->errorResponse('Hata: ' . $e->getMessage());
        }
    }

    /**
     * Iframe görünümü (token ile)
     */
    public function iframe(Request $request, $checkoutId)
    {
        $checkout = Checkout::where(['id' => $checkoutId, 'user_id' => Auth::id()])->first();
        if (!$checkout || !$checkout->paytr_token) {
            return redirect()->route('portal.dashboard')
                ->with('payment_result_status', 'error')
                ->with('payment_result_message', 'Geçersiz ödeme oturumu.');
        }
        return view('portal.paytr.iframe', [
            'iframeUrl' => (new PaytrService())->getIframeEmbedUrl($checkout->paytr_token),
            'token'     => $checkout->paytr_token,
            'testMode'  => (bool) $checkout->test_mode,
        ]);
    }

    /**
     * PayTR sonrası kullanıcının döndüğü ok/fail URL.
     */
    public function paymentResult(Request $request)
    {
        $isFail = $request->query('result') === 'fail' || $request->has('fail_message');
        if ($isFail) {
            return redirect()->route('portal.dashboard')
                ->with('payment_result_status', 'error')
                ->with('payment_result_message', 'Ödemenizi alamadık' . ($request->fail_message ? ': ' . $request->fail_message : '.'));
        }
        return redirect()->route('portal.dashboard')
            ->with('payment_result_status', 'success')
            ->with('payment_result_message', 'Ödemenizi aldık. Siparişiniz işleme alındı.');
    }

    protected function resolveAddress($id)
    {
        $address = \App\Models\UserAddress::where(['id' => $id, 'user_id' => Auth::id()])->first();
        if (!$address) return null;
        return [
            'id'       => $address->id,
            'address'  => $address->address,
            'phone'    => $address->phone ?? null,
            'name'     => $address->name ?? null,
            'city'     => ['title' => optional($address->city)->name ?? ''],
            'district' => ['title' => optional($address->district)->name ?? ''],
            'country'  => ['title' => optional($address->country)->name ?? 'Türkiye'],
        ];
    }
}
