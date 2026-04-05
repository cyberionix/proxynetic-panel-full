<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Order\StoreRequest;
use App\Models\Checkout;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Price;
use App\Models\Product;
use App\Models\User;
use App\Library\Logger;
use App\Traits\AjaxResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Uuid;

class OrderController extends Controller
{
    use AjaxResponses;

    public function index(Request $request)
    {
        $selected_user = '';
        if ($request->user_id) {
            $user_id = $request->user_id;
            $user = User::findOrFail($user_id);
            $selected_user = [
                'label' => $user->first_name . ' ' . $user->last_name,
                'value' => $user->id
            ];
        }
        return view('admin.pages.orders.index', compact('selected_user'));
    }

    public function ajax(Request $request)
    {
        $whereSearch = "orders.deleted_at IS NULL";
        $showAllList = $request->showAllList;

        if ($showAllList) {
            $searchableColumns = [
                "orders.id",
                "orders.id",
                db_user_full_name_expr('users'),
                "orders.id",
                "orders.id",
                "orders.created_at",
                "orders.status"
            ];
        } else {
            $searchableColumns = [
                "orders.id",
                "orders.id",
                "orders.id",
                "orders.id",
                "orders.created_at",
                "orders.status"
            ];
            $userId = $request->userId;
            if ($userId) {
                $whereSearch .= " AND orders.user_id = {$userId} ";
            }
        }

        if (isset($request->order[0]["column"]) and isset($request->order[0]["dir"])) {
            $orderBy = $searchableColumns[$request->order[0]["column"]] . " " . $request->order[0]["dir"];
        } else {
            $orderBy = "orders.id DESC";
        }

        $searchVal = $request->search["value"];
        if ($searchVal) {
            $whereSearch .= " AND (";
            foreach ($searchableColumns as $key => $searchableColumn) {
                $whereSearch .= "$searchableColumn LIKE '%{$searchVal}%'";
                if (array_key_last($searchableColumns) != $key) {
                    $whereSearch .= " OR ";
                } else {
                    $whereSearch .= ")";
                }
            }
        }

        $status = $request->status;
        if ($status) {
            $whereSearch .= " AND orders.status = '{$status}' ";
        }


        $start = $request->start ?? 0;
        $length = $request->length == -1 ? 10 : $request->length;

        $query = Order::select(
            'orders.*',
            DB::raw(db_user_full_name_expr('users').' as user_name'),
        )
            ->with("activeDetail")
            ->leftJoin('users', 'users.id', '=', 'orders.user_id')
            ->whereRaw($whereSearch)
            ->orderByRaw($orderBy);
        $countFilteredRecords = $query->count();
        $query = $query->skip($start)->take($length);

        $list = $query->get();
        $countTotalRecords = $query->count();

        $data = [];
        foreach ($list as $item) {
            $checkbox = '<div class="form-check form-check-sm form-check-custom form-check-solid"><input class="form-check-input bulk-check-order" type="checkbox" value="' . $item->id . '" /></div>';
            $price_data = $item->activeDetail?->price_data;
            $orderUrl = route("admin.orders.show", ["order" => $item->id]);
            $productLabel = $item->product_data["name"] . ' / ' . @$price_data["duration"] . ' ' . convertDurationText(@$price_data["duration_unit"]);
            if ($showAllList) {
                $data[] = [
                    $checkbox,
                    "<span data-id='" . $item->id . "' class='badge badge-sm badge-light-primary'>#" . $item->id . "</span>",
                    "<a href='" . route("admin.users.show", ["user" => $item->user_id]) . "'>" . $item->user_name . "</a>",
                    "<a href='" . $orderUrl . "' class='badge badge-light-primary text-hover-dark'>" . $productLabel . "</a>",
                    "<span class='badge badge-secondary badge-lg'>" . showBalance(@$price_data["price_with_vat"], true) . "</span>",
                    "<span class='badge badge-secondary'>" . $item->created_at->format(defaultDateFormat()) . "</span>",
                    $item->drawDeliveryStatus(),
                    "<a href='" . $orderUrl . "' class='btn btn-light-primary btn-sm'>" . __("view") . "</a>
<a href='javascript:void(0)' class='btn btn-light-danger btn-sm deleteBtn'>" . __("delete") . "</a>"
                ];
            } else {
                $data[] = [
                    $checkbox,
                    "<span data-id='" . $item->id . "' class='badge badge-sm badge-light-primary'>#" . $item->id . "</span>",
                    "<a href='" . $orderUrl . "' class='badge badge-light-primary text-hover-dark'>" . $productLabel . "</a>",
                    "<span class='badge badge-secondary badge-lg'>" . showBalance(@$price_data["price_with_vat"], true) . "</span>",
                    "<span class='badge badge-secondary'>" . $item->created_at->format(defaultDateFormat()) . "</span>",
                    $item->drawStatus(),
                    "<a href='" . $orderUrl . "' class='btn btn-light-primary btn-sm'>" . __("view") . "</a>
<a href='javascript:void(0)' class='btn btn-light-danger btn-sm deleteBtn'>" . __("delete") . "</a>"
                ];
            }
        }

        $response = array(
            'recordsTotal'    => $countTotalRecords,
            'recordsFiltered' => $countFilteredRecords,
            'data'            => $data
        );
        echo json_encode($response);
    }

    public function store(StoreRequest $request)
    {
        DB::beginTransaction();
        try {
            $quantity = $request->quantity;
            $autoDelivery = $request->auto_delivery ? 1 : 0;
            for ($i = 0; $i < $quantity; $i++) {
                $product = Product::with("category")->find($request->product_id);
                if (!$product) {
                    return $this->errorResponse("Geçerli bir ürün seçiniz.");
                }
                $user = User::find($request->user_id);
                if (!$user) {
                    return $this->errorResponse("Geçerli bir kullanıcı seçiniz.");
                }
                $price = Price::find($request->price_id);
                if (!$price || $price->product_id != $product->id) {
                    return $this->errorResponse("Geçerli bir fiyat seçiniz.");
                }

                $productData = $product->toArray();
                unset($productData['deleted_at']);
                unset($productData['created_at']);
                unset($productData['updated_at']);

                $order = Order::create([
                    "order_id"     => Uuid::uuid4()->toString(),
                    "start_date"   => Carbon::createFromFormat(defaultDateFormat(), $request->start_date)->format("Y-m-d"),
                    "end_date"     => Carbon::createFromFormat(defaultDateFormat(), $request->end_date)->format("Y-m-d"),
                    "product_data" => $productData,
                    "product_id"   => $request->product_id,
                    "user_id"      => $user->id,
                ]);

                /* start::SERVICE VALIDATE*/
                $serviceNames = collect($price->product->attrs)->pluck("name")->toArray();
                $serviceData = array_intersect_key($request->all(), array_flip($serviceNames));
                if (in_array("protocol_select", collect($price->product->attrs)->pluck("service_type")->toArray()) && !$serviceData) {
                    return $this->errorResponse("Ek hizmetlerlerden protocol seçimi yapınız.");
                }
                /* end::SERVICE VALIDATE*/


                $servicePrice = 0; //kvd hariç
                $getAdditionalServices = [];
                if ($serviceData) {
                    foreach ($serviceData as $key => $additional_service) {
                        $serviceData = getAdditionalServices($product, $key, $additional_service);
                        $servicePrice += $serviceData["price_without_vat"];
                        $getAdditionalServices[] = $serviceData;
                    }
                }

                $priceData = $price->toArray();
                unset($priceData['deleted_at']);
                unset($priceData['created_at']);
                unset($priceData['updated_at']);
                unset($priceData['product']);
                $priceData['price'] = $price->price_without_vat + $servicePrice;
                $priceData['total_vat'] = ($priceData['price'] * $product->vat_percent) / 100;
                $priceData['price_with_vat'] = $priceData['price'] + $priceData['total_vat'];

                $order_detail = OrderDetail::create([
                    "order_id"            => $order->id,
                    "is_active"           => 1,
                    "is_hidden"           => 0,
                    "additional_services" => $getAdditionalServices,
                    "price_data"          => $priceData,
                    "price_id"            => $price->id
                ]);


                if (isset($request->isPaymentWithTransfer) && $request->isPaymentWithTransfer == 1) {

                    $invoice = Invoice::create([
                        "invoice_number"       => Invoice::generateInvoiceNumber(),
                        "invoice_date"         => Carbon::now(),
                        "due_date"             => Carbon::now(),
                        "total_price"          => $priceData['price_with_vat'],
                        "total_vat"            => $priceData["total_vat"],
                        "total_price_with_vat" => $priceData['price_with_vat'],
                        "status"               => "PAID",
                        "invoice_address"      => $user->getUserInvoiceAddress(),
                        "user_id"              => $user->id
                    ]);

                    $invoice_item = InvoiceItem::create([
                        "type"                 => "NEW",
                        "name"                 => $product->name . " | " . $priceData['duration'] . " " . __(mb_strtolower($priceData['duration_unit'])),
                        "total_price"          => $priceData['price'],
                        "vat_percent"          => $product->vat_percent,
                        "total_price_with_vat" => $priceData['price_with_vat'],
                        "additional_services"  => $getAdditionalServices,
                        "product_id"           => $product->id,
                        "price_id"             => $price->id,
                        "order_id"             => $order->id,
                        "order_detail_id"      => $order_detail->id,
                        "invoice_id"           => $invoice->id
                    ]);

                    Checkout::create([
                        'type'       => 'TRANSFER',
                        'status'     => 'COMPLETED',
                        'amount'     => $priceData['price_with_vat'],
                        'paid_at'    => Carbon::now(),
                        'uuid_value' => Uuid::uuid4()->toString(),
                        'user_id'    => $user->id,
                        'invoice_id' => $invoice->id
                    ]);
                }

                if ($autoDelivery) {
                    $order->approve();
                } else {
                    $order->update([
                        "delivery_status" => "NOT_DELIVERED"
                    ]);
                }
            }
            DB::commit();
            return $this->successResponse('Sipariş bilgileri başarıyla kaydedildi.');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage());
        }
    }

    public function show(Order $order)
    {
        $order->loadMissing(['product', 'user', 'activeDetail', 'activeDetails']);
        $order->maybeHealLocaltonetV4DeliveryStatus();

        if ($order->isPProxyUDelivery() && in_array($order->delivery_status, ['BEING_DELIVERED', 'QUEUED'])) {
            $order->approve();
        }

        $order->refresh();
        $order->loadMissing(['product', 'user', 'activeDetail', 'activeDetails']);

        return view("admin.pages.orders.details.index", compact("order"));
    }

    public function update(Order $order, Request $request)
    {
        DB::beginTransaction();
        try {
            $data = [
                "status"     => $request->status,
                "start_date" => convertDate($request->start_date)
            ];

            $endDateChanged = false;
            $newEndDate = null;

            $durationUnit = $order->activeDetail?->price_data["duration_unit"] ?? null;
            if ($durationUnit !== "ONE_TIME") {
                $newEndDate = convertDate($request->end_date);
                $data["end_date"] = $newEndDate;
                $oldEndDate = $order->end_date ? $order->end_date->format('Y-m-d') : null;
                $endDateChanged = $oldEndDate !== $newEndDate;
            }

            $order->update($data);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse(__("error_response"), ["error" => $e->getMessage()]);
        }

        if ($endDateChanged && $newEndDate && $order->isThreeProxyDelivery()) {
            try {
                $newExpire = $newEndDate . 'T23:59';
                $pi = $order->product_info ?? [];
                $pi['three_proxy_expire'] = $newExpire;
                $order->product_info = $pi;
                $order->save();

                $order->threeProxyExtendExpire($newExpire);
            } catch (\Throwable $e) {
                \App\Library\Logger::error('ADMIN_ORDER_UPDATE_TP_EXPIRE_FAIL', [
                    'order_id' => $order->id,
                    'new_expire' => $newExpire ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $this->successResponse('Sipariş bilgileri başarıyla kaydedildi.', ['redirectUrl' => route('admin.orders.show', ['order' => $order->id])]);
    }

    public function processLocaltonetDeliveryNow(Order $order)
    {
        $order->loadMissing('product');

        if (! $order->isLocaltonetLikeDelivery()) {
            return $this->errorResponse('Bu sipariş Localtonet teslimatı değil.');
        }

        $allowed = ['QUEUED', 'BEING_DELIVERED', 'NOT_DELIVERED'];
        if (! in_array($order->delivery_status, $allowed, true)) {
            return $this->errorResponse('Sipariş kuyrukta değil veya zaten işlendi.');
        }

        if ($order->delivery_status === 'NOT_DELIVERED') {
            $order->forceFill(['delivery_status' => 'QUEUED', 'delivery_error' => null])->save();
        }

        self::dispatchDeliveryInBackground($order->id);

        return $this->successResponse('Teslimat işlemi arka planda başlatıldı. Birkaç dakika içinde sayfayı yenileyin.');
    }

    public static function dispatchDeliveryInBackground(int $orderId): void
    {
        $php     = PHP_BINARY ?: 'php';
        $artisan = base_path('artisan');
        $logFile = base_path('storage/logs/delivery-bg-'.$orderId.'.log');

        \App\Library\Logger::info('DISPATCH_DELIVERY_BG', [
            'order_id' => $orderId,
            'php'      => $php,
        ]);

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $cmd = "start /B \"\" \"{$php}\" \"{$artisan}\" app:deliver-localtonet-single-order {$orderId} > \"{$logFile}\" 2>&1";
        } else {
            $cmd = "{$php} \"{$artisan}\" app:deliver-localtonet-single-order {$orderId} > \"{$logFile}\" 2>&1 &";
        }

        $handle = popen($cmd, 'r');
        if ($handle !== false) {
            pclose($handle);
        } else {
            \App\Library\Logger::error('DISPATCH_DELIVERY_BG_POPEN_FAIL', [
                'order_id' => $orderId,
                'cmd'      => $cmd,
            ]);
        }
    }

    public function completeDelivery(Order $order)
    {
        $appprove = $order->approve();
        if ($appprove) return $this->successResponse("Teslimat başarıyla tamamlandı.");
        return $this->errorResponse("Teslimat sırasında bir sorun oluştu.");
    }

    public function pproxyuUpdateInfo(Order $order, Request $request)
    {
        if (!$order->isPProxyUDelivery()) {
            return $this->errorResponse('Bu sipariş PProxyU teslimatı değil.');
        }

        $validator = Validator::make($request->all(), [
            'pproxyu_pool_ip'   => 'required|string|max:255',
            'pproxyu_pool_port' => 'required|integer|min:1|max:65535',
            'pproxyu_pool_user' => 'required|string|max:255',
            'pproxyu_pool_pass' => 'required|string|max:255',
            'pproxyu_username'  => 'nullable|string|max:255',
            'pproxyu_password'  => 'nullable|string|max:255',
            'pproxyu_days'      => 'nullable|integer|min:1',
            'pproxyu_active_until' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first());
        }

        $pi = $order->product_info ?? [];
        $pi['pproxyu_pool_ip']   = $request->pproxyu_pool_ip;
        $pi['pproxyu_pool_port'] = (int) $request->pproxyu_pool_port;
        $pi['pproxyu_pool_user'] = $request->pproxyu_pool_user;
        $pi['pproxyu_pool_pass'] = $request->pproxyu_pool_pass;

        if ($request->filled('pproxyu_username')) {
            $pi['pproxyu_username'] = $request->pproxyu_username;
        }
        if ($request->filled('pproxyu_password')) {
            $pi['pproxyu_password'] = $request->pproxyu_password;
        }
        if ($request->filled('pproxyu_days')) {
            $pi['pproxyu_days'] = (int) $request->pproxyu_days;
        }
        if ($request->filled('pproxyu_active_until')) {
            $pi['pproxyu_active_until'] = \Carbon\Carbon::parse($request->pproxyu_active_until)->toIso8601String();
        }

        $order->product_info = $pi;

        if ($order->delivery_status !== 'DELIVERED') {
            $order->status = 'ACTIVE';
            $order->delivery_status = 'DELIVERED';
        }

        $order->saveQuietly();

        return $this->successResponse('PProxyU proxy bilgileri başarıyla güncellendi.');
    }

    public function removeDelivery(Order $order)
    {
        $appprove = $order->revokeApproval();
        if ($appprove) return $this->successResponse("Teslimat başarıyla geri alındı.");
        return $this->errorResponse(__("error_response"));
    }

    public function stopTunnels(Order $order)
    {
        if ($order->isThreeProxyDelivery()) {
            $order->threeProxyStopService();
            $pi = $order->product_info ?? [];
            $pi['tunnels_stopped'] = true;
            $order->product_info = $pi;
            $order->save();
            $count = count($order->getAllThreeProxyIds());
            return $this->successResponse($count . ' proxy durduruldu.', ['reload' => true]);
        }

        if (! $order->isLocaltonetLikeDelivery()) {
            return $this->errorResponse('Bu sipariş Localtonet teslimatı değil.');
        }

        $tunnelIds = $order->getAllLocaltonetProxyIds();
        if (count($tunnelIds) === 0) {
            return $this->errorResponse('Durdurulacak tünel bulunamadı.');
        }

        $ok = $order->bulkStopLocaltonetTunnels();
        if ($ok) {
            return $this->successResponse(count($tunnelIds) . ' tünel başarıyla durduruldu.', ['reload' => true]);
        }
        return $this->errorResponse('Tüneller durdurulurken hata oluştu.');
    }

    public function startTunnels(Order $order)
    {
        if ($order->isThreeProxyDelivery()) {
            $order->threeProxyStartService();
            $pi = $order->product_info ?? [];
            $pi['tunnels_stopped'] = false;
            $order->product_info = $pi;
            $order->save();
            $count = count($order->getAllThreeProxyIds());
            return $this->successResponse($count . ' proxy başlatıldı.', ['reload' => true]);
        }

        if (! $order->isLocaltonetLikeDelivery()) {
            return $this->errorResponse('Bu sipariş Localtonet teslimatı değil.');
        }

        $tunnelIds = $order->getAllLocaltonetProxyIds();
        if (count($tunnelIds) === 0) {
            return $this->errorResponse('Başlatılacak tünel bulunamadı.');
        }

        $ok = $order->bulkStartLocaltonetTunnels();
        if ($ok) {
            return $this->successResponse(count($tunnelIds) . ' tünel başarıyla başlatıldı.', ['reload' => true]);
        }
        return $this->errorResponse('Tüneller başlatılırken hata oluştu.');
    }

    public function changeLocaltonetProxyId(Order $order, Request $request)
    {
        DB::beginTransaction();
        try {
            if (!$request->proxyId) return $this->errorResponse("Lütfen geçerli bir proxy id giriniz.");
            $productInfo = $order->product_info;
            $productInfo["proxy_id"] = intval($request->proxyId);

            $data = [
                "product_info" => $productInfo
            ];
            if ($order->delivery_status == "NOT_DELIVERED") {
                $data["status"] = "ACTIVE";
                $data["delivery_status"] = "DELIVERED";
            }
            $order->update($data);
            DB::commit();
            return $this->successResponse(__("edited_response", ["name" => "proxy id"]));
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage());
        }
    }

    public function changeStackProxies(Order $order, Request $request)
    {
        if (!$request->proxies) return $this->errorResponse("Lütfen geçerli proxy adresleri giriniz.");

        $proxies = explode("\n", $request->proxies);
        $proxies = array_map('trim', $proxies);
        $proxies = array_filter($proxies);

        $productInfo = $order->product_info;
        $productInfo["proxy_list"] = $proxies;

        $data = [
            "product_info" => $productInfo
        ];

        if ($order->delivery_status == "NOT_DELIVERED") {
            $data["status"] = "ACTIVE";
            $data["delivery_status"] = "DELIVERED";
        }

        $update = $order->update($data);

        if (!$update) return $this->errorResponse(__("error_response"));
        return $this->successResponse(__("edited_response", ["name" => "proxy adresleri"]));
    }

    public function changeLocaltonetProxyType(Order $order, Request $request)
    {
        if (! $order->isLocaltonetLikeDelivery()) {
            return $this->errorResponse('Sipariş tipi Localtonet / Localtonetv4 olmadığı için işleme devam edilemez.');
        }
        if (!$order->product) return $this->errorResponse("Ürün artık mevcut olmadığı için düzenlemez.");


        $proxy = $order->getProxyLocaltonet();
        if (@$proxy["hasError"] || !isset($proxy["result"]) || @$proxy["result"]["id"] == 0) return $this->errorResponse("Proxy bulunamadı. Siparişin localtonette tanımlı bir proxy idye sahip olduğundan emin olunuz.");

        $proxy = $proxy["result"] ?? [];

        $type = $request->type == "SOCKS" ? "ProxySocks" : "ProxyHttp";
        if ($proxy["protocolType"] == $type) return $this->errorResponse("Mevcut proxy type ile güncellemek istediğiniz proxy type zaten aynı.");

        if ($type == "ProxySocks") {
            $change = $order->changeProxyTypeFromHttpToSocks();
        } else {
            $change = $order->changeProxyTypeFromSocksToHttp();
        }
        if ($change) return $this->successResponse(__("edited_response", ["name" => "Proxy Type"]));
        return $this->errorResponse(__("error_response"));
    }

    public function changeLocaltonetDevice(Order $order, Request $request)
    {
        if ($order->isCanDeliveryType('LOCALTONETV4')) {
            return $this->replaceLocaltonetV4Proxies($order);
        }

        if ($order->changeDevice()) {
            return $this->successResponse('Cihaz başarıyla yenilendi. Proxy bilgileriniz güncellendi.');
        }
        return $this->errorResponse('Cihaz değiştirme işlemi sırasında bir hata oluştu.');
    }

    private function replaceLocaltonetV4Proxies(Order $order)
    {
        $oldIds = $order->getAllLocaltonetProxyIds();

        \App\Models\ProxyLog::logBulk($order, $oldIds, 'REPLACE_DELETE');

        $order->revokeApproval();
        $order->refresh();

        $order->forceFill([
            'delivery_status' => 'QUEUED',
            'delivery_error'  => null,
            'status'          => 'ACTIVE',
        ])->save();

        self::dispatchDeliveryInBackground($order->id);

        return $this->successResponse(
            count($oldIds) . ' proxy silindi, yeni teslimat arka planda başlatıldı.'
        );
    }

    /**
     * Yönetim: fatura beklemeden Localtonet kotası (GB) ve/veya sipariş bitiş süresi (gün) artırır.
     */
    public function applyAdminLocaltonetQuotaAndDuration(Order $order, Request $request)
    {
        if (! $order->isLocaltonetLikeDelivery()) {
            return $this->errorResponse('Bu sipariş Localtonet / Localtonetv4 teslimatı değil.');
        }
        $proxyId = $order->product_info['proxy_id'] ?? null;
        if (!$proxyId) {
            return $this->errorResponse('Siparişte tanımlı tunnel (proxy) bulunamadı.');
        }

        $validator = Validator::make($request->all(), [
            'add_gb' => 'nullable|numeric|min:0.01|max:99999',
            'add_days' => 'nullable|integer|min:1|max:36500',
        ], [
            'add_gb.min' => 'Ek kota en az 0,01 GB olmalıdır.',
            'add_days.min' => 'Ek süre en az 1 gün olmalıdır.',
        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first());
        }
        $validated = $validator->validated();

        $addGb = array_key_exists('add_gb', $validated) && $validated['add_gb'] !== null && $validated['add_gb'] !== ''
            ? (float) $validated['add_gb'] : null;
        $addDays = array_key_exists('add_days', $validated) && $validated['add_days'] !== null && $validated['add_days'] !== ''
            ? (int) $validated['add_days'] : null;

        if (($addGb === null || $addGb <= 0) && ($addDays === null || $addDays <= 0)) {
            return $this->errorResponse('En az ek kota (GB) veya ek süre (gün) girin.');
        }

        Cache::forget('LOCALTONET_PR_DATA_'.$proxyId);

        $service = $order->resolveLocaltonetService();
        $messages = [];

        try {
            if ($addGb !== null && $addGb > 0) {
                $proxy = $order->getProxyLocaltonet();
                if (!$proxy || !isset($proxy['result']['id']) || (int) $proxy['result']['id'] === 0) {
                    throw new \RuntimeException('Tunnel bilgisi alınamadı.');
                }
                $bandwidthLimit = $proxy['result']['bandwidthLimit'] ?? 0;
                if ($bandwidthLimit === 'unlimited') {
                    throw new \RuntimeException('Sınırsız trafikli hizmette kota artırılamaz.');
                }
                $currentBytes = (float) $bandwidthLimit;
                $bwCfg = $order->product_data['delivery_items']['bandwidth_limit'] ?? null;
                $bwCfg = is_array($bwCfg) ? $bwCfg : [];
                [$dataSize, $dataSizeType] = $this->bandwidthLimitPayloadForAdmin($currentBytes, $addGb, $bwCfg);
                if ($dataSize <= 0) {
                    throw new \RuntimeException('Hesaplanan kota geçersiz.');
                }
                $set = $service->setBandwidthLimitForTunnel($proxy['result']['id'], $dataSize, $dataSizeType);
                if (!empty($set['hasError'])) {
                    throw new \RuntimeException('Localtonet kota güncellenemedi.');
                }
                Cache::forget('LOCALTONET_PR_DATA_'.$proxyId);
                $messages[] = number_format($addGb, 2, ',', '.').' GB kota tunnel üzerinde artırıldı.';
            }

            if ($addDays !== null && $addDays > 0) {
                if (!$order->end_date) {
                    throw new \RuntimeException('Sipariş bitiş tarihi yok; süre uzatılamaz.');
                }
                $proxy = $order->getProxyLocaltonet();
                if (!$proxy || !isset($proxy['result']['id']) || (int) $proxy['result']['id'] === 0) {
                    throw new \RuntimeException('Tunnel bilgisi alınamadı.');
                }
                $newEndDate = $order->end_date->copy()->addDays($addDays);
                $getExp = $service->getExpirationDateByTunnelId($proxy['result']['id']);
                if (!$getExp || !empty($getExp['hasError']) || !isset($getExp['result']['expirationDate'])) {
                    throw new \RuntimeException('Localtonet son kullanım tarihi okunamadı.');
                }
                $currentTime = Carbon::parse($getExp['result']['expirationDate'])->format('H:i:s');
                $combined = $newEndDate->format('Y-m-d').' '.$currentTime;
                $newEndDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $combined);
                $setExp = $service->setExpirationDateForTunnel(
                    $proxy['result']['id'],
                    $newEndDateTime->format('Y-m-d\TH:i:s.v\Z')
                );
                if (!empty($setExp['hasError'])) {
                    throw new \RuntimeException('Localtonet bitiş tarihi güncellenemedi.');
                }
                Order::withoutEvents(function () use ($order, $newEndDate) {
                    $order->update(['end_date' => $newEndDate->format('Y-m-d')]);
                });
                Cache::forget('LOCALTONET_PR_DATA_'.$proxyId);
                $messages[] = $addDays.' gün süre eklendi; sipariş bitişi güncellendi.';
            }

            $order->refreshSingleTunnelInDb((int) $proxyId);

            Logger::info('ADMIN_LOCALTONET_QUOTA_DURATION_OK', [
                'order_id' => $order->id,
                'add_gb' => $addGb,
                'add_days' => $addDays,
            ]);

            return $this->successResponse(implode(' ', $messages));
        } catch (\Throwable $e) {
            Logger::error('ADMIN_LOCALTONET_QUOTA_DURATION_FAIL', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * @return array{0: float|int, 1: int}
     */
    private function bandwidthLimitPayloadForAdmin(float $currentBytes, float $additionalGb, ?array $bandwidthLimitConfig): array
    {
        $dataSizeType = (int) (($bandwidthLimitConfig ?? [])['data_size_type'] ?? 4);
        $totalBytes = $currentBytes + ($additionalGb * 1073741824.0);
        if ($totalBytes <= 0) {
            return [0, $dataSizeType];
        }
        if ($dataSizeType === 3) {
            return [(int) max(1, (int) round($totalBytes / 1048576)), $dataSizeType];
        }

        return [(float) round($totalBytes / 1073741824, 2), $dataSizeType];
    }

    public function threeProxyReinstall(Order $order)
    {
        if (!$order->isThreeProxyDelivery()) {
            return $this->errorResponse('Bu sipariş 3Proxy teslimat tipinde değil.');
        }

        $result = $order->threeProxyReinstall();

        if (!empty($result['success'])) {
            return $this->successResponse($result['message'] ?? 'Tekrar kurulum başarılı.');
        }

        return $this->errorResponse($result['message'] ?? 'Tekrar kurulum başarısız.');
    }

    public function threeProxyChangeCredentials(Order $order, Request $request)
    {
        if (!$order->isThreeProxyDelivery()) {
            return $this->errorResponse('Bu sipariş 3Proxy teslimat tipinde değil.');
        }

        $validator = Validator::make($request->all(), [
            'username' => 'required|string|min:3|max:32',
            'password' => 'required|string|min:4|max:64',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first());
        }

        $result = $order->threeProxyChangeCredentials($request->username, $request->password);

        if (!empty($result['success'])) {
            return $this->successResponse($result['message'] ?? 'Kullanıcı/şifre güncellendi.');
        }

        return $this->errorResponse($result['message'] ?? 'Kullanıcı/şifre güncelleme başarısız.');
    }

    public function threeProxyChangePort(Order $order, Request $request)
    {
        if (!$order->isThreeProxyDelivery()) {
            return $this->errorResponse('Bu sipariş 3Proxy teslimat tipinde değil.');
        }

        $validator = Validator::make($request->all(), [
            'http_port' => 'required|integer|min:1|max:65535',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first());
        }

        $socksPort = $request->filled('socks_port') ? (int) $request->socks_port : null;
        $result = $order->threeProxyChangePort((int) $request->http_port, $socksPort);

        if (!empty($result['success'])) {
            return $this->successResponse($result['message'] ?? 'Port güncellendi.');
        }

        return $this->errorResponse($result['message'] ?? 'Port güncelleme başarısız.');
    }

    public function bulkAction(Request $request)
    {
        $ids = $request->input('ids', []);
        $action = $request->input('action');

        if (empty($ids) || !$action) {
            return response()->json(['success' => false, 'message' => 'Geçersiz istek.']);
        }

        $orders = Order::whereIn('id', $ids)->get();
        $count = 0;

        foreach ($orders as $order) {
            switch ($action) {
                case 'mark_active':
                    $order->update(['status' => 'ACTIVE']);
                    $count++;
                    break;
                case 'mark_cancelled':
                    $order->update(['status' => 'CANCELLED']);
                    $count++;
                    break;
                case 'delete':
                    $order->deleteServiceAndRevoke();
                    $order->delete();
                    $count++;
                    break;
            }
        }

        return response()->json(['success' => true, 'message' => "{$count} sipariş işlem gördü."]);
    }

    public function delete(Order $order)
    {
        $order->deleteServiceAndRevoke();
        $del = $order->delete();

        if ($del) return $this->successResponse("Sipariş başarıyla silindi.");
        return $this->errorResponse(__("error_response"));
    }
}
