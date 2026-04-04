<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Support;
use App\Models\SupportMessage;
use App\Models\User;
use App\Services\LocaltonetService;
use App\Traits\AjaxResponses;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    use AjaxResponses;

    public function index()
    {
        return view("portal.pages.orders.index");
    }

    public function ajax(Request $request, User $user)
    {
        $searchableColumns = [
            "orders.id"
        ];

        $whereSearch = "orders.deleted_at IS NULL AND user_id = {$user->id} ";

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

        $start = $request->start ?? 0;
        $length = $request->length == -1 ? 10 : $request->length;

        $query = Order::select(
            'orders.*'
        )
            ->whereRaw($whereSearch)
            ->orderByRaw($orderBy);
        $countFilteredRecords = $query->count();
        $query = $query->skip($start)->take($length);

        $list = $query->get();

        $countTotalRecords = $query->count();
        $data = [];

        foreach ($list as $item) {

            if ($item->user_notes){
                $note_html ='<i data-bs-toggle="tooltip" data-bs-placement="top" title="'.$item->user_notes.'" data-original-note="'.$item->user_notes.'" class=" text-hover-primary update-note-button cursor-pointer fa fs-3 fa-sticky-note text-primary" data-id="'.$item->id.'"></i>';
            }else{
                $note_html ='<i class="fa fs-3 fa-sticky-note text-dark-gray text-hover-primary cursor-pointer update-note-button" data-original-note="'.$item->user_notes.'" data-id="'.$item->id.'"></i>';
            }

            $data[] = [
                "<span data-id='" . $item->id . "' class='badge badge-sm badge-light-primary'>#" . $item->id . "</span>",
                '<div class="text-gray-800 fw-bold">' . ($item->product_data["name"] ?? '-') . ' ' . $note_html . '</div>' . (!empty($item->product_data["category"]["name"]) ? '<span class="text-muted fw-semibold d-block fs-7">' . $item->product_data["category"]["name"] . '</span>' : ''),
                showBalance($item->getTotalAmount(), true),
                $item->end_date?->format(defaultDateFormat()),
                $item->drawDeliveryStatus(),
                $item->drawStatus(),
                "<a href='" . route("portal.orders.show", ["order" => $item->id]) . "' class='btn btn-light-primary btn-sm'>" . __("view") . "</a>"
            ];
        }

        $response = array(
            'recordsTotal' => $countTotalRecords,
            'recordsFiltered' => $countFilteredRecords,
            'data' => $data
        );
        echo json_encode($response);
    }

    public function show(Order $order)
    {
        if ($order->user_id !== Auth::id())
            return redirect(route('portal.dashboard'));

        $order->load(['activeDetails', 'activeDetail', 'product', 'user']);
        $order->maybeHealLocaltonetV4DeliveryStatus();
        $order->refresh();
        $order->loadMissing(['activeDetails', 'activeDetail', 'product', 'user']);

        return view("portal.pages.orders.show.index", compact(["order"]));
    }

    public function upgrade(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id())
            return redirect(route('portal.dashboard'));
        DB::beginTransaction();
        try {
            $upgradePriceId = $request->upgrade_price_id;
            if (!$upgradePriceId) return $this->errorResponse(__("error_response"));

            $upgradePrice = $order->upgradePrices()->find($upgradePriceId);

            $totalPriceWithVat = $upgradePrice->price - $upgradePrice->discount;
            $totalVat = 0;
            if ($upgradePrice->product->vat_percent > 0) {
                $totalPrice = $totalPriceWithVat / (1 + ($upgradePrice->product->vat_percent / 100));
                $totalVat = $totalPriceWithVat - $totalPrice;
            } else {
                $totalPrice = $totalPriceWithVat;
            }

            $invoice = Invoice::create([
                "invoice_number" => Invoice::generateInvoiceNumber(),
                "invoice_date" => Carbon::now(),
                "due_date" => Carbon::now()->addWeek(),
                "status" => "PENDING",
                "total_price" => $totalPrice,
                "total_vat" => $totalVat,
                "total_price_with_vat" => $totalPriceWithVat,
                "user_id" => Auth::id(),
            ]);

            $priceData = $upgradePrice->toArray();
            unset($priceData['deleted_at']);
            unset($priceData['created_at']);
            unset($priceData['updated_at']);
            unset($priceData['product']);
            $priceData['price'] = $totalPrice;
            $priceData['total_vat'] = $totalVat;
            $priceData['price_with_vat'] = $totalPriceWithVat;

            $orderDetail = OrderDetail::create([
                "order_id" => $order->id,
                "is_active" => 0,
                "price_data" => $priceData,
                "price_id" => $priceData["id"],
            ]);

            InvoiceItem::create([
                "type" => "UPGRADE",
                "name" => $upgradePrice->product->name . " | " . $upgradePrice->duration . " " . __(mb_strtolower($upgradePrice->duration_unit)),
                "total_price" => $totalPrice,
                "vat_percent" => $upgradePrice->product->vat_percent,
                "total_price_with_vat" => $totalPriceWithVat,
                "product_id" => $upgradePrice->id,
                "price_id" => $upgradePrice->id,
                "order_id" => $order->id,
                "order_detail_id" => $orderDetail->id,
                "invoice_id" => $invoice->id,
            ]);

            DB::commit();
            return $this->successResponse(__("created_response", ["name" => __("invoice")]) . " " . __("redirecting"), ["redirectUrl" => route("portal.invoices.show", ["invoice" => $invoice->id])]);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse(__("error_response"), ["error" => $e->getMessage()]);
        }
    }

    public function addQuotaPost(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id())
            return redirect(route('portal.dashboard'));
        DB::beginTransaction();
        try {
            $productAttrs = $order->product->findAttrsByServiceType("quota");
            $selectedQuota = collect($productAttrs["options"])->where("value", $request->quota)->first();
            if (!$selectedQuota || !isset($selectedQuota["label"]) || !isset($selectedQuota["value"]) || !isset($selectedQuota["price"])) return $this->errorResponse(__("quota_not_found") . " " . __("refresh_the_page_and_try_again"));

            $totalPriceWithVat = $selectedQuota["price"];
            $totalVat = 0;
            if ($order->product->vat_percent > 0) {
                $totalPrice = $totalPriceWithVat / (1 + ($order->product->vat_percent / 100));
                $totalVat = $totalPriceWithVat - $totalPrice;
            } else {
                $totalPrice = $totalPriceWithVat;
            }

            $invoice = Invoice::create([
                "invoice_number" => Invoice::generateInvoiceNumber(),
                "invoice_date" => Carbon::now(),
                "due_date" => Carbon::now()->addWeek(),
                "status" => "PENDING",
                "total_price" => $totalPrice,
                "total_vat" => $totalVat,
                "total_price_with_vat" => $totalPriceWithVat,
                "user_id" => Auth::id(),
            ]);

            $additionalServices = [
                [
                    "label" => $selectedQuota["label"],
                    "value" => $selectedQuota["value"],
                    "price" => $totalPriceWithVat,
                    "price_without_vat" => $totalPrice
                ]
            ];
            $orderDetail = OrderDetail::create([
                "order_id" => $order->id,
                "is_active" => 0,
                "additional_services" => $additionalServices,
            ]);

            InvoiceItem::create([
                "type" => "ADDITIONAL_QUOTA",
                "name" => $selectedQuota["label"],
                "total_price" => $totalPrice,
                "vat_percent" => $order->product->vat_percent,
                "total_price_with_vat" => $totalPriceWithVat,
                "order_id" => $order->id,
                "order_detail_id" => $orderDetail->id,
                "invoice_id" => $invoice->id
            ]);

            DB::commit();
            return $this->successResponse(__("created_response", ["name" => __("invoice")]) . " " . __("redirecting"), ["redirectUrl" => route("portal.invoices.show", ["invoice" => $invoice->id])]);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse(__("error_response"), ["error" => $e->getMessage()]);
        }
    }

    public function addPProxyQuotaPost(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id())
            return redirect(route('portal.dashboard'));
        DB::beginTransaction();
        try {
            $productAttrs = $order->product->findAttrsByServiceType("pproxy_quota");
            $selectedQuota = collect($productAttrs["options"])->where("value", $request->quota)->first();
            if (!$selectedQuota || !isset($selectedQuota["label"]) || !isset($selectedQuota["value"]) || !isset($selectedQuota["price"])) return $this->errorResponse(__("quota_not_found") . " " . __("refresh_the_page_and_try_again"));

            $totalPriceWithVat = $selectedQuota["price"];
            $totalVat = 0;
            if ($order->product->vat_percent > 0) {
                $totalPrice = $totalPriceWithVat / (1 + ($order->product->vat_percent / 100));
                $totalVat = $totalPriceWithVat - $totalPrice;
            } else {
                $totalPrice = $totalPriceWithVat;
            }

            $invoice = Invoice::create([
                "invoice_number" => Invoice::generateInvoiceNumber(),
                "invoice_date" => Carbon::now(),
                "due_date" => Carbon::now()->addWeek(),
                "status" => "PENDING",
                "total_price" => $totalPrice,
                "total_vat" => $totalVat,
                "total_price_with_vat" => $totalPriceWithVat,
                "user_id" => Auth::id(),
            ]);

            $additionalServices = [
                [
                    "label" => $selectedQuota["label"],
                    "value" => $selectedQuota["value"],
                    "price" => $totalPriceWithVat,
                    "price_without_vat" => $totalPrice
                ]
            ];
            $orderDetail = OrderDetail::create([
                "order_id" => $order->id,
                "is_active" => 0,
                "additional_services" => $additionalServices,
            ]);

            InvoiceItem::create([
                "type" => "PPROXY_ADDITIONAL_QUOTA",
                "name" => $selectedQuota["label"],
                "total_price" => $totalPrice,
                "vat_percent" => $order->product->vat_percent,
                "total_price_with_vat" => $totalPriceWithVat,
                "order_id" => $order->id,
                "order_detail_id" => $orderDetail->id,
                "invoice_id" => $invoice->id
            ]);

            DB::commit();
            return $this->successResponse(__("created_response", ["name" => __("invoice")]) . " " . __("redirecting"), ["redirectUrl" => route("portal.invoices.show", ["invoice" => $invoice->id])]);
        } catch (\Exception $e) {
            DB::rollback();
            \App\Library\Logger::error('PPROXY_ADD_QUOTA_FAIL', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return $this->errorResponse($e->getMessage());
        }
    }

    public function addQuotaDurationPost(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id())
            return redirect(route('portal.dashboard'));
        DB::beginTransaction();
        try {
            $productAttrs = $order->product->findAttrsByServiceType("quota_duration");
            $selectedQuotaDuration = collect($productAttrs["options"])->where("value", $request->quota_duration)->first();
            if (!$selectedQuotaDuration || !isset($selectedQuotaDuration["label"]) || !isset($selectedQuotaDuration["value"]) || !isset($selectedQuotaDuration["gb"]) || !isset($selectedQuotaDuration["duration"]) || !isset($selectedQuotaDuration["duration_unit"]) || !isset($selectedQuotaDuration["price"])) return $this->errorResponse(__("quota_duration_not_found") . " " . __("refresh_the_page_and_try_again"));

            $totalPriceWithVat = $selectedQuotaDuration["price"];
            $totalVat = 0;
            if ($order->product->vat_percent > 0) {
                $totalPrice = $totalPriceWithVat / (1 + ($order->product->vat_percent / 100));
                $totalVat = $totalPriceWithVat - $totalPrice;
            } else {
                $totalPrice = $totalPriceWithVat;
            }

            $invoice = Invoice::create([
                "invoice_number" => Invoice::generateInvoiceNumber(),
                "invoice_date" => Carbon::now(),
                "due_date" => Carbon::now()->addWeek(),
                "status" => "PENDING",
                "total_price" => $totalPrice,
                "total_vat" => $totalVat,
                "total_price_with_vat" => $totalPriceWithVat,
                "user_id" => Auth::id(),
            ]);


            $additionalServices = [
                [
                    "label" => $selectedQuotaDuration["label"],
                    "value" => $selectedQuotaDuration["value"],
                    "gb" => $selectedQuotaDuration["gb"],
                    "duration" => $selectedQuotaDuration["duration"],
                    "duration_unit" => $selectedQuotaDuration["duration_unit"],
                    "price" => $totalPriceWithVat,
                    "price_without_vat" => $totalPrice
                ]
            ];
            $orderDetail = OrderDetail::create([
                "order_id" => $order->id,
                "is_active" => 0,
                "additional_services" => $additionalServices,
            ]);

            InvoiceItem::create([
                "type" => "ADDITIONAL_QUOTA_DURATION",
                "name" => $selectedQuotaDuration["label"],
                "total_price" => $totalPrice,
                "vat_percent" => $order->product->vat_percent,
                "total_price_with_vat" => $totalPriceWithVat,
                "order_id" => $order->id,
                "order_detail_id" => $orderDetail->id,
                "invoice_id" => $invoice->id
            ]);

            DB::commit();
            return $this->successResponse(__("created_response", ["name" => __("invoice")]) . " " . __("redirecting"), ["redirectUrl" => route("portal.invoices.show", ["invoice" => $invoice->id])]);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse(__("error_response"), ["error" => $e->getMessage()]);
        }
    }

    public function tpExtraDurationPost(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) return redirect(route('portal.dashboard'));
        if (!$order->isThreeProxyDelivery()) return $this->errorResponse('Bu sipariş 3Proxy tipi değil.');

        $order->loadMissing('product');
        if (!$order->product) return $this->errorResponse('Ürün bulunamadı.');

        DB::beginTransaction();
        try {
            $productAttrs = $order->product->findAttrsByServiceType("tp_extra_duration");
            if (!$productAttrs) return $this->errorResponse('Ek süre hizmeti bu ürün için tanımlı değil.');

            $selected = collect($productAttrs["options"])->where("value", $request->tp_extra_duration)->first();
            if (!$selected) return $this->errorResponse('Seçilen süre seçeneği bulunamadı.');

            $totalPriceWithVat = (float) $selected["price"];
            $vatPercent = (float) ($order->product->vat_percent ?? 0);
            $totalVat = 0;
            if ($vatPercent > 0) {
                $totalPrice = $totalPriceWithVat / (1 + ($vatPercent / 100));
                $totalVat = $totalPriceWithVat - $totalPrice;
            } else {
                $totalPrice = $totalPriceWithVat;
            }

            $invoice = Invoice::create([
                "invoice_number" => Invoice::generateInvoiceNumber(),
                "invoice_date" => Carbon::now(),
                "due_date" => Carbon::now()->addWeek(),
                "status" => "PENDING",
                "total_price" => $totalPrice,
                "total_vat" => $totalVat,
                "total_price_with_vat" => $totalPriceWithVat,
                "user_id" => Auth::id(),
            ]);

            $additionalServices = [[
                "label" => $selected["label"],
                "value" => $selected["value"],
                "duration" => $selected["duration"],
                "duration_unit" => $selected["duration_unit"],
                "price" => $totalPriceWithVat,
                "price_without_vat" => $totalPrice,
            ]];

            $orderDetail = OrderDetail::create([
                "order_id" => $order->id,
                "is_active" => 0,
                "additional_services" => $additionalServices,
            ]);

            InvoiceItem::create([
                "type" => "TP_EXTRA_DURATION",
                "name" => $selected["label"],
                "total_price" => $totalPrice,
                "vat_percent" => $vatPercent,
                "total_price_with_vat" => $totalPriceWithVat,
                "order_id" => $order->id,
                "order_detail_id" => $orderDetail->id,
                "invoice_id" => $invoice->id,
            ]);

            DB::commit();
            return $this->successResponse(__("created_response", ["name" => __("invoice")]) . " " . __("redirecting"), ["redirectUrl" => route("portal.invoices.show", ["invoice" => $invoice->id])]);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('Hata: ' . $e->getMessage());
        }
    }

    public function tpServiceActionPost(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) return redirect(route('portal.dashboard'));
        if (!$order->isThreeProxyDelivery()) return $this->errorResponse('Bu sipariş 3Proxy tipi değil.');

        $order->loadMissing('product');
        if (!$order->product) return $this->errorResponse('Ürün bulunamadı.');

        $actionType = $request->input('action_type');
        $validTypes = ['tp_change_ips', 'tp_subnet_ips', 'tp_class_ips'];
        if (!in_array($actionType, $validTypes)) return $this->errorResponse('Geçersiz hizmet tipi.');

        $attrData = collect($order->product->attrs ?? [])->where("service_type", $actionType)->first();
        if (!$attrData) return $this->errorResponse('Bu hizmet ürün için tanımlı değil.');

        if (in_array($actionType, ['tp_subnet_ips', 'tp_class_ips'])) {
            $pool = $order->resolveThreeProxyPool();
            if ($pool) {
                $allIps = $pool->getAllIpsWithServer();
                $di = $order->product->delivery_items ?? [];
                $deliveryCount = max(1, (int) ($di['delivery_count'] ?? 1));
                if ($actionType === 'tp_subnet_ips') {
                    $available = count(selectIpsBySubnet($allIps, $deliveryCount));
                } else {
                    $available = count(selectIpsByClass($allIps, $deliveryCount));
                }
                if ($available < $deliveryCount) {
                    $label = $actionType === 'tp_subnet_ips' ? '/24 subnet' : '/16 class blok';
                    return $this->errorResponse("Havuzda yeterli farklı {$label} yok. Gerekli: {$deliveryCount}, Mevcut: {$available}.");
                }
            }
        }

        DB::beginTransaction();
        try {
            $totalPriceWithVat = (float) ($attrData["price"] ?? 0);
            $vatPercent = (float) ($order->product->vat_percent ?? 0);
            $totalVat = 0;
            if ($vatPercent > 0) {
                $totalPrice = $totalPriceWithVat / (1 + ($vatPercent / 100));
                $totalVat = $totalPriceWithVat - $totalPrice;
            } else {
                $totalPrice = $totalPriceWithVat;
            }

            $labels = [
                'tp_change_ips' => "IP'leri Değiştir",
                'tp_subnet_ips' => "Her Subnetten Farklı IP",
                'tp_class_ips' => "Her Class IP'den Farklı IP",
            ];

            $invoice = Invoice::create([
                "invoice_number" => Invoice::generateInvoiceNumber(),
                "invoice_date" => Carbon::now(),
                "due_date" => Carbon::now()->addWeek(),
                "status" => "PENDING",
                "total_price" => $totalPrice,
                "total_vat" => $totalVat,
                "total_price_with_vat" => $totalPriceWithVat,
                "user_id" => Auth::id(),
            ]);

            $additionalServices = [[
                "label" => $labels[$actionType] ?? $actionType,
                "value" => $actionType,
                "service_type" => $actionType,
                "price" => $totalPriceWithVat,
                "price_without_vat" => $totalPrice,
            ]];

            $orderDetail = OrderDetail::create([
                "order_id" => $order->id,
                "is_active" => 0,
                "additional_services" => $additionalServices,
            ]);

            InvoiceItem::create([
                "type" => "TP_SERVICE_ACTION",
                "name" => $labels[$actionType] ?? $actionType,
                "total_price" => $totalPrice,
                "vat_percent" => $vatPercent,
                "total_price_with_vat" => $totalPriceWithVat,
                "order_id" => $order->id,
                "order_detail_id" => $orderDetail->id,
                "invoice_id" => $invoice->id,
            ]);

            DB::commit();
            return $this->successResponse(__("created_response", ["name" => __("invoice")]) . " " . __("redirecting"), ["redirectUrl" => route("portal.invoices.show", ["invoice" => $invoice->id])]);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse(__("error_response"), ["error" => $e->getMessage()]);
        }
    }

    public function updateNote(Order $order,Request $request)
    {
        if ($order->user_id !== Auth::id())
            return [Auth::user(),$order->user()];

        if (Str::length($request->note) >= 100)
            return $this->errorResponse('Not alanı 100 karakterden fazla olamaz.');

        $order->user_notes = $request->note;
        $order->save();
        return $this->successResponse('Sipariş notu başarıyla güncellendi.');

    }
}
