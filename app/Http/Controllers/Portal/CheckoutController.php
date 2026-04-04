<?php

namespace App\Http\Controllers\Portal;

use App\Events\CheckoutConfirmed;
use App\Http\Controllers\Controller;
use App\Library\Logger;
use App\Models\ActivityLog;
use App\Models\Admin;
use App\Models\BalanceActivity;
use App\Models\Checkout;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserAddress;
use App\Notifications\CheckoutConfirmedNotification;
use App\Notifications\InvoiceCheckoutConfirmedNotification;
use App\Notifications\NewCheckoutNotificationForAdmin;
use App\Services\PaytrService;
use App\Traits\AjaxResponses;
use Carbon\Carbon;
use Exception;
use Google\Service\AIPlatformNotebooks\DataDisk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Ramsey\Uuid\Uuid;
use function Symfony\Component\Translation\t;

class CheckoutController extends Controller
{
    use AjaxResponses;

    //start::paytr
    public function checkout(Request $request)
    {

        if (Auth::user()->security->is_limit_payment_methods == 1 && !in_array("CREDIT_CARD", Auth::user()->security->payment_methods)){
            return $this->errorResponse("Kredi/Banka kartı ile ödeme yapamazsınız. Lütfen diğer ödeme yöntemleri ile devam ediniz.");
        }
        $request->validate([
            'card_name' => 'required',
            'card_number' => 'required|numeric|digits:16',
            'card_exp_month' => 'required|numeric',
            'card_exp_year' => 'required|numeric|digits:2',
            'card_cvv' => 'required|numeric|digits:3',
            "invoice_address_id" => [
                "required",
                Rule::exists('user_addresses', 'id')->where(function ($query) {
                    $query->where('user_id', Auth::id());
                }),
            ],
        ], [
            "card_name.required" => __('card_information_is_incorrect'),
            "card_number.required" => __('card_information_is_incorrect'),
            "card_number.numeric" => __('card_information_is_incorrect'),
            "card_number.digits" => __('card_information_is_incorrect'),
            "card_exp_month.required" => __('card_information_is_incorrect'),
            "card_exp_month.numeric" => __('card_information_is_incorrect'),
            "card_exp_year.required" => __('card_information_is_incorrect'),
            "card_exp_year.numeric" => __('card_information_is_incorrect'),
            "card_exp_year.digits" => __('card_information_is_incorrect'),
            "card_cvv.required" => __('card_information_is_incorrect'),
            "card_cvv.numeric" => __('card_information_is_incorrect'),
            "card_cvv.digits" => __('card_information_is_incorrect'),
            "invoice_address_id.required" => __('custom_field_is_required', ['name' => __('invoice_address')]),
            "invoice_address_id.exists" => __('please_choose_a_valid_address'),
        ]);

        DB::beginTransaction();
        try {
            $invoiceAddressData = $this->getUserInvoiceAddress($request->invoice_address_id);
            if (!$invoiceAddressData) {
                return [
                    'success' => false,
                    'message' => __('please_choose_a_valid_address')
                ];
            }
            $netToken = Uuid::uuid4()->toString();

            $setBasket = [];
            $totalPriceWithVat = 0;

            if ($request->invoice_id) {
                $invoice = Invoice::find($request->invoice_id);
                if (!$invoice) {
                    return $this->errorResponse(__("invoice_not_found") . " " . __("refresh_the_page_and_try_again"));
                }
                $totalPriceWithVat = $invoice->total_price_with_vat;

                $invoice = Invoice::where(["id" => $request->invoice_id, "user_id" => Auth::id()])->first();
                if (!$invoice) {
                    return [
                        'success' => false,
                        'message' => "Fatura bulunamadı. Sayfayı yenileyip tekrar deneyiniz."
                    ];
                }

                $invoice->update([
                    "invoice_address" => $invoiceAddressData
                ]);

                $checkout = Checkout::create([
                    'type' => 'CREDIT_CARD',
                    'status' => '3DS_REDIRECTED',
                    'amount' => $invoice->total_price_with_vat,
                    'uuid_value' => $netToken,
                    'invoice_id' => $invoice->id,
                    'user_id' => Auth::id(),
                    "extra_params" => [
                        "invoice_address_data" => $invoiceAddressData
                    ]
                ]);

                foreach ($invoice->items as $item) {
                    $setBasket[] = [$item->name, $item->total_price_with_vat, 1];
                }


                $checkout_amount = $totalPriceWithVat;
            } else {
                $basket = Auth::user()->basket;
                if (!$basket || count($basket->items) <= 0) {
                    return $this->errorResponse(__("you_must_add_at_least_one_item_to_your_cart"));
                }

                $checkout = Checkout::create([
                    'type' => 'CREDIT_CARD',
                    'status' => '3DS_REDIRECTED',
                    'uuid_value' => $netToken,
                    'channel' => 'PAYTR',
                    'user_id' => Auth::id(),
                    'basket_id' => $basket->id
                ]);
                if (!$checkout) {
                    return [
                        'success' => false,
                        'message' => 'Sistemsel bir hata oluştu. Hata kodu: #1000043'
                    ];
                }

                $basketPricesData = [];
                foreach ($basket->items as $item) {
                    $servicePrice = 0; //kvd hariç
                    $getAdditionalServices = [];
                    if ($item->additional_services) {
                        foreach ($item->additional_services as $key => $additional_service) {
                            $serviceData = $item->getAdditionalServices($key, $additional_service);
                            $getAdditionalServices[] = $serviceData;
                            $servicePrice += $serviceData["price_without_vat"];
                        }
                    }
                    $priceData = $item->price->toArray();
                    $priceData['price'] = $item->price->price_without_vat + $servicePrice;
                    $priceData['total_vat'] = ($priceData['price'] * $item->product->vat_percent) / 100;
                    $priceData['price_with_vat'] = $priceData['price'] + $priceData['total_vat'];

                    $totalPriceWithVat += $priceData['price_with_vat'];
                    $setBasket[] = [$item->product->name, $priceData['price_with_vat'], 1];

                    $priceData["product"]["additional_services"] = $getAdditionalServices;
                    $basketPricesData[] = $priceData;
                }
                $basketSummary = $basket->basketSummary();
                $checkout_amount = $basketSummary['real_total'] ?? 0;
                $checkout->update([
                    "amount" => $basketSummary['real_total'],
                    "extra_params" => [
                        "price_data" => $basketPricesData,
                        "invoice_address_data" => $invoiceAddressData
                    ]
                ]);
            }

            $data = [
                "cc_owner" => $request->card_name,
                "card_number" => $request->card_number,
                "expiry_month" => $request->card_exp_month,
                "expiry_year" => $request->card_exp_year,
                "cvv" => $request->card_cvv,
                "name" => Auth::user()->full_name,
                "email" => Auth::user()->email,
                "address" => @$invoiceAddressData["address"] . " " . @$invoiceAddressData["city"]["title"] . "/" . @$invoiceAddressData["district"]["title"] . " - " . @$invoiceAddressData["country"]["title"],
                "phone" => Auth::user()->phone ?? '905000101010',
                "checkout_id" => $checkout->id+51
            ];

            $paymentArea = (new PaytrService())->sendData($data, $checkout_amount, $setBasket);
            DB::commit();
            return $paymentArea;
            return $this->successResponse("", ["paymentAreaHtml" => base64_encode($paymentArea)]);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage());
        }
    }

    public function paymentResult(Request $request)
    {
        if (isset($request->fail_message)) {
            return redirect()->route("portal.dashboard")->with('payment_result_status','error')->with("payment_result_message", "Ödemenizi alamadık. Hata mesajı ".$request->fail_message);
        } else {
            return redirect()->route("portal.dashboard")->with('payment_result_status','success')->with("payment_result_message", "Ödemenizi aldık.");
        }
    }
    //end::paytr

    public function saveBankTransferNotification(Request $request)
    {
        if (Auth::user()->security->is_limit_payment_methods == 1 && !in_array("TRANSFER", Auth::user()->security->payment_methods)){
            return $this->errorResponse("Havale/EFT ile ödeme yapamazsınız. Lütfen diğer ödeme yöntemleri ile devam ediniz.");
        }
        $request->validate([
            "invoice_address_id" => [
                "required",
                Rule::exists('user_addresses', 'id')->where(function ($query) {
                    $query->where('user_id', Auth::id());
                }),
            ],
        ], [
            "invoice_address_id.required" => __('custom_field_is_required', ['name' => __('invoice_address')]),
            "invoice_address_id.exists" => __('please_choose_a_valid_address'),
        ]);

        DB::beginTransaction();
        try {
            $user = Auth::user();

            $reference_code = Checkout::getNextReferenceCode();
            $netToken = Uuid::uuid4()->toString();

            $invoiceAddressData = $this->getUserInvoiceAddress($request->invoice_address_id);
            if (!$invoiceAddressData) {
                return [
                    'success' => false,
                    'message' => __('please_choose_a_valid_address')
                ];
            }

            if ($request->invoice_id) {
                $invoice = Invoice::where(["id" => $request->invoice_id, "user_id" => Auth::id()])->first();
                if (!$invoice) {
                    return [
                        'success' => false,
                        'message' => "Fatura bulunamadı. Sayfayı yenileyip tekrar deneyiniz."
                    ];
                }

                $invoice->update([
                    "invoice_address" => $invoiceAddressData
                ]);

                Checkout::create([
                    'type' => 'TRANSFER',
                    'status' => 'WAITING_APPROVAL',
                    'amount' => $invoice->total_price_with_vat,
                    'reference_code' => $reference_code,
                    'paid_at' => Carbon::now(),
                    'uuid_value' => $netToken,
                    'invoice_id' => $invoice->id,
                    'user_id' => Auth::id()
                ]);
                \App\Services\NotificationTemplateService::send('invoice_pending_approval', $user, [
                    'fatura_no' => $invoice->invoice_number ?? $invoice->id,
                    'tutar' => number_format($invoice->total_price_with_vat ?? 0, 2, ',', '.'),
                    'fatura_url' => url('/invoices/' . $invoice->id),
                ]);
            } else {
                $basket = $user->basket;
                if (!$basket || count($basket->items) <= 0) {
                    return [
                        'success' => false,
                        'message' => __("you_must_add_at_least_one_item_to_your_cart") . " " . __("refresh_the_page_and_try_again")
                    ];
                }

                $basketSummary = $basket->basketSummary();

                $invoice = Invoice::create([
                    "invoice_number" => Invoice::generateInvoiceNumber(),
                    "invoice_date" => Carbon::now(),
                    "due_date" => Carbon::now()->addWeek(),
                    "status" => "PENDING",
                    "invoice_address" => $invoiceAddressData,
                    "user_id" => Auth::id()
                ]);

                $checkout = Checkout::create([
                    'type' => 'TRANSFER',
                    'status' => 'WAITING_APPROVAL',
                    'amount' => $basketSummary['real_total'],
                    'reference_code' => $reference_code,
                    'paid_at' => Carbon::now(),
                    'uuid_value' => $netToken,
                    'basket_id' => $basket->id,
                    'invoice_id' => $invoice->id,
                    'user_id' => Auth::id()
                ]);

                $__total_amount = 0;
                foreach ($basket->items as $item) {
                    $servicePrice = 0; //kvd hariç
                    $getAdditionalServices = [];
                    if ($item->additional_services) {
                        foreach ($item->additional_services as $key => $additional_service) {
                            $serviceData = $item->getAdditionalServices($key, $additional_service);
                            $getAdditionalServices[] = $serviceData;
                            $servicePrice += $serviceData["price_without_vat"];
                        }
                    }

                    $priceData = $item->price->toArray();
                    unset($priceData['deleted_at']);
                    unset($priceData['created_at']);
                    unset($priceData['updated_at']);
                    unset($priceData['product']);
                    $priceData['price'] = $item->price->price_without_vat + $servicePrice;
                    $priceData['total_vat'] = ($priceData['price'] * $item->product->vat_percent) / 100;
                    $priceData['price_with_vat'] = $priceData['price'] + $priceData['total_vat'];

                    $__total_amount += $priceData['price_with_vat'];

                    $productData = $item->product->toArray();
                    unset($productData['deleted_at']);
                    unset($productData['created_at']);
                    unset($productData['updated_at']);

                    $order = new Order();
                    $order->product_data = $productData;
                    $order->order_id = Uuid::uuid4()->toString();
                    $order->start_date = Carbon::now();
                    $order->end_date = addDurationToDate($item->price->duration, $item->price->duration_unit, Carbon::now());
                    $order->status = 'PENDING';
                    $order->product_id = $item->product->id;
                    $order->user_id = Auth::id();
                    $order->save();

                    $orderDetailId = OrderDetail::create([
                        "order_id" => $order->id,
                        "is_active" => 1,
                        "is_hidden" => 1,
                        "additional_services" => $getAdditionalServices,
                        "price_data" => $priceData,
                        "price_id" => $item->price->id,
                        "checkout_id" => $checkout->id
                    ]);

                    InvoiceItem::create([
                        "type" => "NEW",
                        "name" => $item->product->name . " | " . $priceData['duration'] . " " . __(mb_strtolower($priceData['duration_unit'])),
                        "total_price" => $priceData['price'],
                        "vat_percent" => $item->product->vat_percent,
                        "total_price_with_vat" => $priceData['price_with_vat'],
                        "additional_services" => $getAdditionalServices,
                        "product_id" => $item->product->id,
                        "price_id" => $item->price->id,
                        "order_id" => $order->id,
                        "order_detail_id" => $orderDetailId->id,
                        "invoice_id" => $invoice->id
                    ]);
                }

//                $checkout->update(["amount" => $__total_amount]);

                $basketSummary = $basket->basketSummary();
                $invoice->update([
                    "total_price" => $basketSummary["sub_total"],
                    "total_vat" => $basketSummary["tax"],
                    "total_price_with_vat" => $basketSummary["total"],
                    'discount_amount' => $basketSummary['discount_amount'],
                    'real_total' => $basketSummary["real_total"],
                    'coupon_code_id' => $basketSummary['coupon_code'],
                    'coupon_code_text' => $basketSummary['coupon_code_text']
                ]);

                $basket->update(["completed_at" => Carbon::now()]);
                $basket->delete();
            }
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Ödeme bildiriminiz başarıyla kaydedili. Mesai saatlerinde yoğunluğa bağlı olarak 0-2 saat aralığında ödemeniz onaylanacaktır. Farklı sorularınız var ise bize ulaşmaktan lütfen çekinmeyin. 😊',
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse("Ödeme bildiriminiz alınırken sistemsel bir hata oluştu. Hata kodu: #10002053", ["error" => $e->getMessage()]);
        }
    }

    public function paymentWithBalance(Request $request)
    {
        if (Auth::user()->security->is_limit_payment_methods == 1 && !in_array("WALLET", Auth::user()->security->payment_methods)){
            return $this->errorResponse("Kredi Bakiyesi ile ödeme yapamazsınız. Lütfen diğer ödeme yöntemleri ile devam ediniz.");
        }
        $request->validate([
            "invoice_address_id" => [
                "required",
                Rule::exists('user_addresses', 'id')->where(function ($query) {
                    $query->where('user_id', Auth::id());
                }),
            ],
        ], [
            "invoice_address_id.required" => __('custom_field_is_required', ['name' => __('invoice_address')]),
            "invoice_address_id.exists" => __('please_choose_a_valid_address'),
        ]);

        DB::beginTransaction();
        try {
            $user = Auth::user();
            $netToken = Uuid::uuid4()->toString();

            $invoiceAddressData = $this->getUserInvoiceAddress($request->invoice_address_id);
            if (!$invoiceAddressData) {
                return [
                    'success' => false,
                    'message' => __('please_choose_a_valid_address')
                ];
            }

            if ($request->invoice_id) {
                $invoice = Invoice::where(["id" => $request->invoice_id, "user_id" => Auth::id()])->first();
                if (!$invoice) {
                    return [
                        'success' => false,
                        'message' => "Fatura bulunamadı. Sayfayı yenileyip tekrar deneyiniz."
                    ];
                }

                if (round($invoice->total_price_with_vat, 2) > round($user->balance, 2)) {
                    return $this->errorResponse("Yetersiz kredi bakiyesi.<br><br><a href='" . route("portal.balance.index") . "' class='badge badge-primary'><i class='fa fa-plus text-white me-1'></i>Kredi Yükle</a>");
                }

                if ($invoice->status === 'PAID') {
                    return $this->errorResponse('Bu fatura zaten ödenmiş. Sayfayı yenileyin.');
                }

                $invoice->update([
                    "invoice_address" => $invoiceAddressData,
                    "status" => "PAID",
                ]);

                $checkout = Checkout::create([
                    'type' => 'BALANCE',
                    'status' => 'COMPLETED',
                    'amount' => $invoice->total_price_with_vat,
                    'paid_at' => Carbon::now(),
                    'uuid_value' => $netToken,
                    'invoice_id' => $invoice->id,
                    'user_id' => Auth::id()
                ]);

                $user->update([
                    "balance" => $user->balance - $invoice->total_price_with_vat
                ]);
                BalanceActivity::create([
                    "user_id" => $user->id,
                    "type" => "OUT",
                    "amount" => $invoice->total_price_with_vat,
                    "model" => "invoice",
                    "model_id" => $invoice->id
                ]);
            } else {
                $basket = $user->basket;
                if (!$basket || count($basket->items) <= 0) {
                    return [
                        'success' => false,
                        'message' => __("you_must_add_at_least_one_item_to_your_cart") . " " . __("refresh_the_page_and_try_again")
                    ];
                }

                $basketSummary = $basket->basketSummary();

                if (round($basketSummary["real_total"], 2) > round($user->balance, 2)) {
                    return $this->errorResponse("Yetersiz kredi bakiyesi.<br><br><a href='" . route("portal.balance.index") . "' class='badge badge-primary'><i class='fa fa-plus text-white me-1'></i>Kredi Yükle</a>");
                }

                $invoice = Invoice::create([
                    "invoice_number" => Invoice::generateInvoiceNumber(),
                    "invoice_date" => Carbon::now(),
                    "due_date" => Carbon::now(),
                    "status" => "PAID",
                    "invoice_address" => $invoiceAddressData,
                    "user_id" => Auth::id()
                ]);

                $checkout = Checkout::create([
                    'type' => 'BALANCE',
                    'status' => 'COMPLETED',
                    'amount' => $basketSummary["real_total"],
                    'paid_at' => Carbon::now(),
                    'uuid_value' => $netToken,
                    'basket_id' => $basket->id,
                    'invoice_id' => $invoice->id,
                    'user_id' => Auth::id()
                ]);

                foreach ($basket->items as $item) {
                    $servicePrice = 0; //kvd hariç
                    $getAdditionalServices = [];
                    if ($item->additional_services) {
                        foreach ($item->additional_services as $key => $additional_service) {
                            $serviceData = $item->getAdditionalServices($key, $additional_service);
                            $servicePrice += $serviceData["price_without_vat"];
                            $getAdditionalServices[] = $serviceData;
                        }
                    }
                    $priceData = $item->price->toArray();
                    unset($priceData['deleted_at']);
                    unset($priceData['created_at']);
                    unset($priceData['updated_at']);
                    unset($priceData['product']);
                    $priceData['price'] = $item->price->price_without_vat + $servicePrice;
                    $priceData['total_vat'] = ($priceData['price'] * $item->product->vat_percent) / 100;
                    $priceData['price_with_vat'] = $priceData['price'] + $priceData['total_vat'];


                    $productData = $item->product->toArray();
                    unset($productData['deleted_at']);
                    unset($productData['created_at']);
                    unset($productData['updated_at']);

                    $order = new Order();
                    $order->product_data = $productData;
                    $order->order_id = Uuid::uuid4()->toString();
                    $order->start_date = Carbon::now();
                    $order->end_date = addDurationToDate($item->price->duration, $item->price->duration_unit, Carbon::now());
                    $order->status = 'PENDING';
                    $order->product_id = $item->product->id;
                    $order->user_id = Auth::id();
                    $order->is_test_product = $item->is_test_product ? 1 : 0;
                    $order->save();

                    $orderDetailId = OrderDetail::create([
                        "order_id" => $order->id,
                        "is_active" => 1,
                        "is_hidden" => 0,
                        "additional_services" => $getAdditionalServices,
                        "price_data" => $priceData,
                        "price_id" => $item->price->id,
                        "checkout_id" => $checkout->id
                    ]);

                    InvoiceItem::create([
                        "type" => "NEW",
                        "name" => $item->product->name . " | " . $priceData['duration'] . " " . __(mb_strtolower($priceData['duration_unit'])),
                        "total_price" => $priceData['price'],
                        "vat_percent" => $item->product->vat_percent,
                        "total_price_with_vat" => $priceData['price_with_vat'],
                        "additional_services" => $getAdditionalServices,
                        "product_id" => $item->product->id,
                        "price_id" => $item->price->id,
                        "order_id" => $order->id,
                        "order_detail_id" => $orderDetailId->id,
                        "invoice_id" => $invoice->id
                    ]);
                }

                $checkout->update(["amount" => $basketSummary["total"]]);
                $invoice->update([
                    "total_price" => $basketSummary["sub_total"],
                    "total_vat" => $basketSummary["tax"],
                    "total_price_with_vat" => $basketSummary["total"],
                    'discount_amount' => $basketSummary['discount_amount'],
                    'real_total' => $basketSummary["real_total"],
                    'coupon_code_id' => $basketSummary['coupon_code'],
                    'coupon_code_text' => $basketSummary['coupon_code_text']
                ]);

                $basket->update(["completed_at" => Carbon::now()]);
                $basket->delete();

                $user->update([
                    "balance" => $user->balance - $basketSummary["real_total"]
                ]);
                BalanceActivity::create([
                    "user_id" => $user->id,
                    "type" => "OUT",
                    "amount" => $basketSummary["real_total"],
                    "model" => "invoice",
                    "model_id" => $invoice->id
                ]);
            }

            DB::commit();

            $response = response()->json([
                'success' => true,
                'message' => 'Tebrikler! Siparişiniz onaylandı..',
            ]);

            // sync kuyruk + uzun Localtonet teslimi aynı istekte tarayıcı/nginx zaman aşımına düşer; JSON dönmeden önce bitmeli.
            $deferredQueue = config('queue.checkout_deferred_connection', 'database');
            app()->terminating(function () use ($checkout, $invoice, $deferredQueue) {
                try {
                    event(new CheckoutConfirmed($checkout));
                    $checkout->loadMissing('user');
                    if ($checkout->user) {
                        $notification = new InvoiceCheckoutConfirmedNotification($invoice);
                        $notification->onConnection($deferredQueue);
                        $checkout->user->notify($notification);
                        \App\Services\NotificationTemplateService::send('invoice_paid', $checkout->user, [
                            'fatura_no' => $invoice->invoice_number ?? $invoice->id,
                            'tutar' => number_format($invoice->total ?? 0, 2, ',', '.'),
                            'fatura_url' => url('/invoices/' . $invoice->id),
                        ]);
                    }
                } catch (\Throwable $e) {
                    Logger::error('CHECKOUT_CONFIRMED_AFTER_RESPONSE', [
                        'checkout_id' => $checkout->id,
                        'user_id' => $checkout->user_id,
                        'message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            });

            return $response;
        } catch (\Exception $e) {
            DB::rollBack();
            Logger::error("PAYMENT_WITH_BALANCE", ["user_id" => Auth::id(), "error" => $e]);
            return $this->errorResponse("Ödeme alınırken sistemsel bir hata oluştu. Hata kodu: #PWB10002054");
        }
    }

    public function getUserInvoiceAddress($invoiceAddressId = null)
    {
        $invoiceAddress = UserAddress::with(["district", "city", "country"])->find($invoiceAddressId);
        if (!$invoiceAddress) {
            return false;
        }

        return [
            "invoice_type" => $invoiceAddress["invoice_type"] ?? null,
            "address" => $invoiceAddress["address"] ?? null,
            "district" => [
                "id" => $invoiceAddress["district"]["id"] ?? null,
                "title" => $invoiceAddress["district"]["title"] ?? null
            ],
            "city" => [
                "id" => $invoiceAddress["city"]["id"] ?? null,
                "title" => $invoiceAddress["city"]["title"] ?? null
            ],
            "country" => [
                "id" => $invoiceAddress["country"]["id"] ?? null,
                "title" => $invoiceAddress["country"]["title"] ?? null
            ],
            "tax_number" => $invoiceAddress["tax_number"] ?? null,
            "tax_office" => $invoiceAddress["tax_office"] ?? null,
            "company_name" => $invoiceAddress["company_name"] ?? null,
        ];
    }
}
