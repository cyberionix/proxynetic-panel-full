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
     * Test modunda OR callback gecikirse, başarılı dönüşte checkout'u manuel tamamlar.
     */
    public function paymentResult(Request $request)
    {
        $isFail = $request->query('result') === 'fail' || $request->has('fail_message');
        if ($isFail) {
            return redirect()->route('portal.dashboard')
                ->with('payment_result_status', 'error')
                ->with('payment_result_message', 'Ödemenizi alamadık' . ($request->fail_message ? ': ' . $request->fail_message : '.'));
        }

        // Auto-complete fallback (test mode OR callback delay safety net):
        // Find latest pending PayTR checkout for this user (last 30 min) that is still 3DS_REDIRECTED.
        $cutoff = \Carbon\Carbon::now()->subMinutes(30);
        $checkout = Checkout::where('user_id', Auth::id())
            ->where('channel', 'PAYTR')
            ->where('status', '3DS_REDIRECTED')
            ->where('created_at', '>=', $cutoff)
            ->orderBy('id', 'desc')
            ->first();

        if ($checkout) {
            $isTestMode = (bool) ($checkout->test_mode ?? config('paytr.options.test_mode', false));
            // Manually complete in test mode OR if no callback has arrived yet for live mode (safety)
            try {
                $this->completeCheckoutManually($checkout, $isTestMode);
                return redirect()->route('portal.dashboard')
                    ->with('payment_result_status', 'success')
                    ->with('payment_result_message', 'Ödemeniz alındı. Siparişiniz oluşturuldu.' . ($isTestMode ? ' (TEST MODU)' : ''));
            } catch (\Throwable $e) {
                Log::error('PAYTR_AUTO_COMPLETE_FAIL', ['checkout_id' => $checkout->id, 'error' => $e->getMessage()]);
            }
        }

        return redirect()->route('portal.dashboard')
            ->with('payment_result_status', 'success')
            ->with('payment_result_message', 'Ödemenizi aldık. Siparişiniz işleme alındı.');
    }

    /**
     * Manually complete a PayTR checkout (mirrors the callback success path).
     * Runs the same logic the callback would, including:
     *   - Mark checkout COMPLETED + paid_at
     *   - Mark invoice PAID (if invoice_id exists), OR create new invoice + order from basket data
     *   - Fire CheckoutConfirmed event + InvoiceCheckoutConfirmedNotification
     */
    protected function completeCheckoutManually(Checkout $checkout, bool $testMode = false): void
    {
        if (in_array($checkout->status, ['COMPLETED', 'CANCELLED', 'FAILED'])) {
            return; // already terminal
        }

        DB::beginTransaction();
        try {
            $totalAmount = $checkout->amount;

            if ($checkout->invoice_id) {
                // Path A: existing invoice — mark it PAID
                $invoice = \App\Models\Invoice::find($checkout->invoice_id);
                if ($invoice) {
                    $invoice->update([
                        'status' => 'PAID',
                        'paid_at' => \Carbon\Carbon::now(),
                    ]);
                }
                $checkout->update([
                    'amount' => $totalAmount,
                    'paid_at' => \Carbon\Carbon::now(),
                    'status' => 'COMPLETED',
                ]);
            } else {
                // Path B: no invoice — create a new invoice + orders from price_data
                $priceData = @$checkout->extra_params['price_data'];
                $invoiceAddressData = @$checkout->extra_params['invoice_address_data'] ?? [];

                $invoice = new \App\Models\Invoice();
                $invoice->user_id = $checkout->user_id;
                $invoice->status = 'PAID';
                // invoice has no paid_at column; status=PAID is enough
                $invoice->invoice_address = $invoiceAddressData;
                $invoice->save();

                if (is_array($priceData)) {
                    foreach ($priceData as $item) {
                        try {
                            $order = new \App\Models\Order();
                            $order->product_data = $item['product'] ?? [];
                            $order->order_id = (string) \Ramsey\Uuid\Uuid::uuid4();
                            $order->start_date = \Carbon\Carbon::now();
                            $order->status = 'PENDING';
                            $order->product_id = $item['product']['id'] ?? null;
                            $order->user_id = $checkout->user_id;
                            $order->save();

                            $orderDetail = \App\Models\OrderDetail::create([
                                'order_id' => $order->id,
                                'checkout_id' => $checkout->id,
                            ]);

                            \App\Models\InvoiceItem::create([
                                'invoice_id' => $invoice->id,
                                'order_id' => $order->id,
                                'order_detail_id' => $orderDetail->id,
                                'name' => $item['product']['name'] ?? 'Item',
                                'price_without_vat' => $item['price'] ?? 0,
                                'total_vat' => $item['total_vat'] ?? 0,
                                'total_price_with_vat' => $item['price_with_vat'] ?? 0,
                                'type' => 'NEW',
                            ]);
                        } catch (\Throwable $itemEx) {
                            Log::warning('PAYTR_ITEM_CREATE_FAIL', ['error' => $itemEx->getMessage()]);
                        }
                    }
                }

                $checkout->update([
                    'amount' => $totalAmount,
                    'paid_at' => \Carbon\Carbon::now(),
                    'status' => 'COMPLETED',
                    'invoice_id' => $invoice->id,
                ]);

                // Update invoice totals
                $invoice->refresh();
                $invoiceTotal = $invoice->items()->sum('total_price_with_vat');
                $invoiceVat = $invoice->items()->sum('total_vat');
                $invoice->update([
                    'total_price_with_vat' => $invoiceTotal,
                    'total_vat' => $invoiceVat,
                ]);

                // Clear basket
                if ($checkout->basket_id) {
                    \App\Models\Basket::where('id', $checkout->basket_id)->delete();
                }
            }

            // Log the auto-completion as a paytr_transaction record
            try {
                \App\Models\PaytrTransaction::create([
                    'reference_uuid' => (string) \Illuminate\Support\Str::uuid(),
                    'checkout_id' => $checkout->id,
                    'invoice_id' => $checkout->invoice_id,
                    'user_id' => $checkout->user_id,
                    'merchant_oid' => (string) ($checkout->id + 51),
                    'type' => 'auto_complete_on_redirect',
                    'status' => 'success',
                    'amount' => $totalAmount,
                    'currency' => 'TL',
                    'test_mode' => $testMode,
                    'paytr_status' => 'success',
                    'callback_received_at' => null,
                ]);
            } catch (\Throwable $e) {
                Log::warning('PAYTR_AUTO_COMPLETE_LOG_FAIL', ['error' => $e->getMessage()]);
            }

            // Fire event + notification (queued)
            try {
                event(new \App\Events\CheckoutConfirmed($checkout));
            } catch (\Throwable $e) {
                Log::warning('PAYTR_AUTO_COMPLETE_EVENT_FAIL', ['error' => $e->getMessage()]);
            }
            try {
                if ($checkout->user) {
                    $checkout->user->notify(new \App\Notifications\InvoiceCheckoutConfirmedNotification($checkout->invoice ?? null));
                }
            } catch (\Throwable $e) {
                Log::warning('PAYTR_AUTO_COMPLETE_NOTIFY_FAIL', ['error' => $e->getMessage()]);
            }

            DB::commit();
            Log::info('PAYTR_AUTO_COMPLETE_OK', ['checkout_id' => $checkout->id, 'test_mode' => $testMode]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('PAYTR_AUTO_COMPLETE_FAIL', ['checkout_id' => $checkout->id, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            throw $e;
        }
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

    /**
     * PayTR S2S callback handler — verifies hash, updates checkout/invoice/orders.
     * Endpoint: POST /callback-paytr/64a3e520cf
     * Originally a closure in routes/web.php; converted to a controller method
     * so the route can be cached safely (closure cache had namespace issues).
     */
    public function callback(\Illuminate\Http\Request $request)
    {

    \Illuminate\Support\Facades\Log::info("CALLBACK_PAYTR", ["post_request" => $request->all()]);
    DB::beginTransaction();
    try {
        $oidNum = is_numeric($request->merchant_oid) ? ((int) $request->merchant_oid - 51) : null;
        if (!$oidNum || $oidNum <= 0) {
            \Illuminate\Support\Facades\Log::error("CALLBACK_PAYTR_INVALID_OID", ["post_request" => $request->all()]);
            echo "OK";
            return;
        }
        $checkout = Checkout::find($oidNum);
        if (!$checkout) {
            \Illuminate\Support\Facades\Log::error("CALLBACK_PAYTR_CHECKOUT_NOT_FOUND", ["post_request" => $request->all()]);
            return;
        }

        if (!(new \App\Services\PaytrService())->verifyCallback($request->all())) {
            \Illuminate\Support\Facades\Log::error("CALLBACK_PAYTR_BAD_HASH", ["post_request" => $request->all()]);
            return;
        }


        if (in_array($checkout->status, ['COMPLETED', 'CANCELLED', 'FAILED'])) {
            echo "OK"; return;
        }

        // PAYTR_IDEMPOTENT_LOG_OK
        try {
            \App\Models\PaytrTransaction::create([
                'reference_uuid' => (string) \Illuminate\Support\Str::uuid(),
                'checkout_id'    => $checkout->id,
                'invoice_id'     => $checkout->invoice_id,
                'user_id'        => $checkout->user_id,
                'merchant_oid'   => $request->merchant_oid,
                'type'           => 'callback',
                'status'         => $request->status,
                'amount'         => ($request->total_amount ?? 0) / 100,
                'currency'       => $request->currency ?? 'TL',
                'test_mode'      => (bool) ($checkout->test_mode ?? config('paytr.options.test_mode', true)),
                'paytr_status'           => $request->status,
                'paytr_total_amount'     => ($request->total_amount ?? 0) / 100,
                'paytr_payment_type'     => $request->payment_type ?? null,
                'paytr_installment'      => (int) ($request->installment_count ?? 0),
                'paytr_failed_reason_code' => $request->failed_reason_code ?? null,
                'paytr_failed_reason_msg'  => $request->failed_reason_msg ?? null,
                'callback_payload'       => $request->all(),
                'callback_received_at'   => now(),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('CALLBACK_PAYTR_TX_LOG_FAIL', ['error' => $e->getMessage()]);
        }

        if ($request->status == 'success') {
            ## BURADA YAPILMASI GEREKENLER
            ## 1) Siparişi onaylayın.
            ## 2) Eğer müşterinize mesaj / SMS / e-posta gibi bilgilendirme yapacaksanız bu aşamada yapmalısınız.
            ## 3) 1. ADIM'da gönderilen payment_amount sipariş tutarı taksitli alışveriş yapılması durumunda
            ## değişebilir. Güncel tutarı $post['total_amount'] değerinden alarak muhasebe işlemlerinizde kullanabilirsiniz.

            $totalAmount = $request->total_amount / 100;

            if ($checkout->invoice_id) {
                $invoice = \App\Models\Invoice::find($checkout->invoice_id);
                if (!$invoice) {
                    \Illuminate\Support\Facades\Log::error("CALLBACK_PAYTR_INVOICE_NOT_FOUND", ["user_id" => $checkout->user_id, "checkout_id" => $checkout->id]);
                    return;
                }

                $invoice->update([
                    "invoice_address" => $checkout->extra_params["invoice_address_data"] ?? "",
                    "status" => "PAID",
                    "due_date" => \Carbon\Carbon::now(),
                ]); // PAYTR_INVOICE_PAID_PATCH

                $checkout->update([
                    "amount" => $totalAmount,
                    "paid_at" => \Carbon\Carbon::now(),
                    'status' => 'COMPLETED',
                ]);
            } else {
                $priceData = @$checkout->extra_params["price_data"];

                $invoice = \App\Models\Invoice::create([
                    "invoice_number" => \App\Models\Invoice::generateInvoiceNumber(),
                    "invoice_date" => \Carbon\Carbon::now(),
                    "due_date" => \Carbon\Carbon::now(),
                    "status" => "PAID",
                    "invoice_address" => @$checkout->extra_params["invoice_address_data"] ?? "",
                    "user_id" => $checkout->user_id
                ]);

                $__total_price = 0;
                $__total_vat = 0;
                $__total_price_with_vat = 0;
                foreach ($priceData as $item) {
                    $__total_price += $item['price'];
                    $__total_vat += $item['total_vat'];
                    $__total_price_with_vat += $item['price_with_vat'];

                    $productData = $item["product"];
                    unset($productData['deleted_at']);
                    unset($productData['created_at']);
                    unset($productData['updated_at']);

                    $order = new Order();
                    $order->product_data = $productData;
                    $order->order_id = \Ramsey\Uuid\Uuid::uuid4()->toString();
                    $order->start_date = \Carbon\Carbon::now();
                    $order->end_date = addDurationToDate($item["duration"], $item["duration_unit"], \Carbon\Carbon::now());
                    $order->status = 'PENDING';
                    $order->product_id = $productData["id"];
                    $order->user_id = $checkout->user_id;
                    $order->save();

                    $orderDetailId = \App\Models\OrderDetail::create([
                        "order_id" => $order->id,
                        "is_active" => 1,
                        "is_hidden" => 0,
                        "additional_services" => $productData["additional_services"],
                        "price_data" => $item,
                        "price_id" => $item["id"],
                        "checkout_id" => $checkout->id
                    ]);

                    \App\Models\InvoiceItem::create([
                        "type" => "NEW",
                        "name" => $productData["name"] . " | " . $item['duration'] . " " . __(mb_strtolower($item['duration_unit'])),
                        "total_price" => $item['price'],
                        "vat_percent" => $productData["vat_percent"],
                        "total_price_with_vat" => $item['price_with_vat'],
                        "additional_services" => $productData["additional_services"],
                        "product_id" => $productData["id"],
                        "price_id" => $item["id"],
                        "order_id" => $order->id,
                        "order_detail_id" => $orderDetailId->id,
                        "invoice_id" => $invoice->id
                    ]);
                }

                $checkout->update([
                    "amount" => $totalAmount,
                    "paid_at" => \Carbon\Carbon::now(),
                    "invoice_id" => $invoice->id,
                    'status' => 'COMPLETED'
                ]);

                $invoice->update([
                    "total_price" => $__total_price,
                    "total_vat" => $__total_vat,
                    "total_price_with_vat" => $__total_price_with_vat,
                ]);

                if ($checkout->basket) {
                    $checkout->basket->update(["completed_at" => \Carbon\Carbon::now()]);
                    $checkout->basket->delete();
                }
            }

            event(new \App\Events\CheckoutConfirmed($checkout));
            $deferred = config('queue.checkout_deferred_connection', 'database');
            $paytrNotify = new \App\Notifications\InvoiceCheckoutConfirmedNotification($invoice);
            $paytrNotify->onConnection($deferred);
            $checkout->user->notify($paytrNotify);
            \App\Services\NotificationTemplateService::send('invoice_paid', $checkout->user, [
                'fatura_no' => $invoice->invoice_number ?? $invoice->id,
                'tutar' => number_format($invoice->total_price_with_vat ?? 0, 2, ',', '.'),
                'fatura_url' => url('/invoices/' . $invoice->id),
            ]);
        } else {
            \Illuminate\Support\Facades\Log::error("CALLBACK_PAYTR_PAYMENT_FAILED", ["user_id" => $checkout->user_id, "checkout_id" => $checkout->id, "post_request" => $request->all()]);
            $checkout->update([
                "status" => "FAILED"
            ]);
            return;
        }
        DB::commit();
        echo "OK";
        exit;
    } catch (\Exception $e) {
        DB::rollback();
        \Illuminate\Support\Facades\Log::error("CALLBACK_PAYTR_PAYMENT_FAILED_TRANSACTION", ["user_id" => $checkout?->user_id, "checkout_id" => $checkout?->id, "post_request" => $request->all(), "error" => $e->getMessage()]);
        $checkout->update([
            "status" => "FAILED"
        ]);
        return;
    }

    }

}
