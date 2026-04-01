<?php

use App\Events\CheckoutConfirmed;
use App\Models\Checkout;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Notifications\InvoiceCheckoutConfirmedNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Ramsey\Uuid\Uuid;
use App\Library\Logger;

Route::get('/testxa', function () {
    $ppId = '1303310';
    $ppToken = 'TkRReE4yRXhZbVkwWVdGaU5tRmhPVEkyTWpGalpXSTRZelpsWVdKaFpHVTFOV1JqTnpjek13PT0=';
    return view('test',compact('ppId','ppToken'));
});

Route::get('/adeneme',function(){
    $localtonetService = new \App\Services\LocaltonetService();
//    return $localtonetService->getTunnelDetail('524875');

    $order = Order::find('1347');
   return $order->changeDevice();
    $order = Order::find('1322');

    return $order->getProxyLocaltonet();
    return $localtonetService->getTunnelDetail('524875');
    return $order->changeDevice();

    $set = $localtonetService->setBandwidthLimitForTunnel('524875','5','4');


    return $localtonetService->getTunnelDetail('524875');

   $order = Order::latest()->first();
    return $order->product_info['proxy_id'];
});

Route::get('/dashboard', function () {
    return redirect()->route('portal.dashboard');
});
Route::get('/auth/login', function () {
    return redirect()->route('portal.auth.login');
});
Route::get('/', [\App\Http\Controllers\Web\HomeController::class, 'index'])->name('web.home');
Route::get('/netAdminLogin', function () {
    if (\Illuminate\Support\Facades\Auth::guard('admin')->check()) {
        return redirect(route('admin.dashboard'));
    } else {
        return redirect(route('admin.auth.login'));
    }
})->name("admin");

Route::get('/tt',function (){
return \App\Models\Product::testProducts();
});

Route::post('/callback-paytr/64a3e520cf', function (Illuminate\Http\Request $request) {
    Logger::info("CALLBACK_PAYTR", ["post_request" => $request->all()]);
    DB::beginTransaction();
    try {
        $checkout = Checkout::find($request->merchant_oid-51);
        if (!$checkout) {
            Logger::error("CALLBACK_PAYTR_CHECKOUT_NOT_FOUND", ["post_request" => $request->all()]);
            exit();
        }

        $hash = base64_encode(hash_hmac('sha256', $request->merchant_oid . env("MERCENT_SALT") . $request->status . $request->total_amount, env("MERCENT_KEY"), true));
        if ($hash != $request->hash) {
            Logger::error("CALLBACK_PAYTR_BAD_HASH", ["post_request" => $request->all()]);
            exit();
        }

        if (in_array($checkout->status, ['COMPLETED', 'CANCELLED', 'FAILED'])) {
            echo "OK";
            exit();
        }

        if ($request->status == 'success') {
            ## BURADA YAPILMASI GEREKENLER
            ## 1) Siparişi onaylayın.
            ## 2) Eğer müşterinize mesaj / SMS / e-posta gibi bilgilendirme yapacaksanız bu aşamada yapmalısınız.
            ## 3) 1. ADIM'da gönderilen payment_amount sipariş tutarı taksitli alışveriş yapılması durumunda
            ## değişebilir. Güncel tutarı $post['total_amount'] değerinden alarak muhasebe işlemlerinizde kullanabilirsiniz.

            $totalAmount = $request->total_amount / 100;

            if ($checkout->invoice_id) {
                $invoice = Invoice::find($checkout->invoice_id);
                if (!$invoice) {
                    Log::error("CALLBACK_PAYTR_INVOICE_NOT_FOUND", ["user_id" => $checkout->user_id, "checkout_id" => $checkout->id]);
                    exit();
                }

                $invoice->update([
                    "invoice_address" => $checkout->extra_params["invoice_address_data"] ?? "",
                ]);

                $checkout->update([
                    "amount" => $totalAmount,
                    "paid_at" => Carbon::now(),
                    'status' => 'COMPLETED',
                ]);
            } else {
                $priceData = @$checkout->extra_params["price_data"];

                $invoice = Invoice::create([
                    "invoice_number" => Invoice::generateInvoiceNumber(),
                    "invoice_date" => Carbon::now(),
                    "due_date" => Carbon::now(),
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
                    $order->order_id = Uuid::uuid4()->toString();
                    $order->start_date = Carbon::now();
                    $order->end_date = addDurationToDate($item["duration"], $item["duration_unit"], Carbon::now());
                    $order->status = 'PENDING';
                    $order->product_id = $productData["id"];
                    $order->user_id = $checkout->user_id;
                    $order->save();

                    $orderDetailId = OrderDetail::create([
                        "order_id" => $order->id,
                        "is_active" => 1,
                        "is_hidden" => 0,
                        "additional_services" => $productData["additional_services"],
                        "price_data" => $item,
                        "price_id" => $item["id"],
                        "checkout_id" => $checkout->id
                    ]);

                    InvoiceItem::create([
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
                    "paid_at" => Carbon::now(),
                    "invoice_id" => $invoice->id,
                    'status' => 'COMPLETED'
                ]);

                $invoice->update([
                    "total_price" => $__total_price,
                    "total_vat" => $__total_vat,
                    "total_price_with_vat" => $__total_price_with_vat,
                ]);

                if ($checkout->basket) {
                    $checkout->basket->update(["completed_at" => Carbon::now()]);
                    $checkout->basket->delete();
                }
            }

            event(new CheckoutConfirmed($checkout));
            $deferred = config('queue.checkout_deferred_connection', 'database');
            $paytrNotify = new InvoiceCheckoutConfirmedNotification($invoice);
            $paytrNotify->onConnection($deferred);
            $checkout->user->notify($paytrNotify);
        } else {
            Log::error("CALLBACK_PAYTR_PAYMENT_FAILED", ["user_id" => $checkout->user_id, "checkout_id" => $checkout->id, "post_request" => $request->all()]);
            $checkout->update([
                "status" => "FAILED"
            ]);
            exit();
        }
        DB::commit();
        echo "OK";
        exit;
    } catch (\Exception $e) {
        DB::rollback();
        Log::error("CALLBACK_PAYTR_PAYMENT_FAILED_TRANSACTION", ["user_id" => $checkout?->user_id, "checkout_id" => $checkout?->id, "post_request" => $request->all(), "error" => $e->getMessage()]);
        $checkout->update([
            "status" => "FAILED"
        ]);
        exit();
    }
});


Route::get('/kvkk-aydinlatma-metni', function () {
})->name('web.gdpr');


Route::get('/verify-email-otp/{email}/{code}', [\App\Http\Controllers\Portal\AuthController::class, 'verifyEmailOTP'])->name('auth.verify_email_otp');

