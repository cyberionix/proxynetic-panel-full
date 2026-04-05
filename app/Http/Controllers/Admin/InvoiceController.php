<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Invoice\UpdateRequest;
use App\Library\EInvoiceManager;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Price;
use App\Models\Product;
use App\Models\User;
use App\Traits\AjaxResponses;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;

class InvoiceController extends Controller
{
    use AjaxResponses;

    public function index()
    {
        return view("admin.pages.invoices.index");
    }

    public function create(Request $request)
    {
        $invoiceNumber = '00000000';

        $selected_user = User::find($request->user_id);

        if ($selected_user) {
            $selected_user = [
                'label' => $selected_user->id . " | " . $selected_user->first_name . ' ' . $selected_user->last_name,
                'value' => $selected_user->id
            ];
        }

        return view('admin.pages.invoices.create', compact('invoiceNumber', 'selected_user'));
    }

    public function productSearch(Request $request)
    {
        $term = isset($request->term["term"]) ? $request->term["term"] : '';
        $result = [];

        $products = Product::where("name", 'LIKE', '%' . $term . '%')->orWhere('id', 'LIKE', '%' . $term . '%')
            ->limit(50)
            ->orderByDesc("id")
            ->get();
        foreach ($products as $product) {
            $result[] = [
                "id"   => $product->id,
                "name" => $product->name
            ];
        }

        return response()->json([
            "items" => $result
        ]);
    }

    public function productFind(Request $request)
    {
        $id = $request->id;
        $response = [];
        $response = Product::findOrFail($id);
        $response['prices'] = Price::whereProductId($id)->get();
        return $this->successResponse("", ["data" => $response]);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $user_id = $request->user_id;
            $invoiceData = $request->only("invoice_number", "user_id");
            $invoiceData["invoice_date"] = convertDate($request->invoice_date);

            $invoiceAddress = $request->invoice_address;
            if ($invoiceAddress) {
                $invoiceData["invoice_address"] = [
                    "invoice_type" => $invoiceAddress["invoice_type"] ?? null,
                    "address"      => $invoiceAddress["address"] ?? null,
                    "district"     => [
                        "id"    => $invoiceAddress["district"]["id"] ?? null,
                        "title" => $invoiceAddress["district"]["title"] ?? null
                    ],
                    "city"         => [
                        "id"    => $invoiceAddress["city"]["id"] ?? null,
                        "title" => $invoiceAddress["city"]["title"] ?? null
                    ],
                    "country"      => [
                        "id"    => $invoiceAddress["country"]["id"] ?? null,
                        "title" => $invoiceAddress["country"]["title"] ?? null
                    ],
                    "tax_number"   => $invoiceAddress["tax_number"] ?? null,
                    "tax_office"   => $invoiceAddress["tax_office"] ?? null,
                    "company_name" => $invoiceAddress["company_name"] ?? null,
                ];
            }

            if (!$invoiceData["invoice_address"]['city']['id'] || !$invoiceData["invoice_address"]['district']['id']) {
                return $this->errorResponse('Geçersiz adres bilgisi.');
            }
            /* PASTE START */
            $items = $request->product;
            if (!$items || count($items) <= 0) {
                return [
                    'success' => false,
                    'message' => 'En az bir ürün eklemelisiniz.'
                ];
            }

            $invoice = Invoice::create([
                "invoice_number"  => Invoice::generateInvoiceNumber(),
                "invoice_date"    => Carbon::now(),
                "due_date"        => Carbon::now()->addWeek(),
                "status"          => "PENDING",
                "invoice_address" => $invoiceAddress,
                "user_id"         => $request->user_id
            ]);

            $__total_amount = 0;
            $_total_price = 0;
            $_total_vat = 0;
            $_total_price_with_vat = 0;
            foreach ($items['id'] as $key => $product) {
                $amount = $request->product['amount'][$key] ? commaToDot($request->product['amount'][$key]) : null;
                $product_name = $request->product['name'][$key] ?? null;
                $id = $request->product['id'][$key] ?? null;
                $price_id = $request->product['price_id'][$key] ?? null;
                $quantity = $request->product['quantity'][$key] ?? 1;
                $vat_percent = $request->product['vat_percent'][$key] ?? null;

                if (!$amount || !$product_name) continue;

                $servicePrice = 0; //kdv hariç
                $getAdditionalServices = [];
//                if (isset($item->additional_services) && $item->additional_services) {
//                    foreach ($item->additional_services as $key => $additional_service) {
//                        $serviceData = $item->getAdditionalServices($key, $additional_service);
//                        $getAdditionalServices[] = $serviceData;
//                        $servicePrice += $serviceData["price_without_vat"];
//                    }
//                }



                $priceWithoutVat = $amount / (1 + ($vat_percent / 100));
                $priceData['price_with_vat'] = $amount;
                $priceData['price'] = $priceWithoutVat;
                $priceData['total_vat'] = ($priceWithoutVat * $vat_percent) / 100;

                $_total_price += $priceWithoutVat;
                $_total_price_with_vat += $priceData['price_with_vat'];
                $_total_vat += $priceData['total_vat'];

                $__total_amount += $priceData['price_with_vat'];


                InvoiceItem::create([
                    "type"                 => "CUSTOM",
                    "name"                 => $product_name,
                    "total_price"          => $priceData['price'],
                    "vat_percent"          => $vat_percent,
                    "total_price_with_vat" => $priceData['price_with_vat'],
                    "additional_services"  => $getAdditionalServices,
                    "product_id"           => null,
                    "price_id"             => null,
                    "order_id"             => null,
                    "order_detail_id"      => null,
                    "invoice_id"           => $invoice->id
                ]);
            }

//            $checkout->update(["amount" => $__total_amount]);


            if (!$_total_price_with_vat){
                DB::rollBack();
                return $this->errorResponse('Tutar 0 TLden büyük olmalıdır.');
            }
            $invoice->update([
                "total_price"          => $_total_price,
                "total_vat"            => $_total_vat,
                "total_price_with_vat" => $_total_price_with_vat,
            ]);

            /* PASTE END*/
            DB::commit();
            return $this->successResponse(__("created_response", ["name" => __("invoice")]), ["redirectUrl" => route("admin.invoices.show", ["invoice" => $invoice->id])]);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage() . ' - ' . $e->getLine());
        }
    }

    public function storeBackup(Request $request)
    {
        DB::beginTransaction();
        try {
            $user_id = $request->user_id;
            $invoiceData = $request->only("invoice_number", "user_id");
            $invoiceData["invoice_date"] = convertDate($request->invoice_date);

            $invoiceAddress = $request->invoice_address;
            if ($invoiceAddress) {
                $invoiceData["invoice_address"] = [
                    "invoice_type" => $invoiceAddress["invoice_type"] ?? null,
                    "address"      => $invoiceAddress["address"] ?? null,
                    "district"     => [
                        "id"    => $invoiceAddress["district"]["id"] ?? null,
                        "title" => $invoiceAddress["district"]["title"] ?? null
                    ],
                    "city"         => [
                        "id"    => $invoiceAddress["city"]["id"] ?? null,
                        "title" => $invoiceAddress["city"]["title"] ?? null
                    ],
                    "country"      => [
                        "id"    => $invoiceAddress["country"]["id"] ?? null,
                        "title" => $invoiceAddress["country"]["title"] ?? null
                    ],
                    "tax_number"   => $invoiceAddress["tax_number"] ?? null,
                    "tax_office"   => $invoiceAddress["tax_office"] ?? null,
                    "company_name" => $invoiceAddress["company_name"] ?? null,
                ];
            }

            if (!$invoiceData["invoice_address"]['city']['id'] || !$invoiceData["invoice_address"]['district']['id']) {
                return $this->errorResponse('Geçersiz adres bilgisi.');
            }
            /* PASTE START */
            $items = $request->product;
            if (!$items || count($items) <= 0) {
                return [
                    'success' => false,
                    'message' => 'En az bir ürün eklemelisiniz.'
                ];
            }

            $invoice = Invoice::create([
                "invoice_number"  => Invoice::generateInvoiceNumber(),
                "invoice_date"    => Carbon::now(),
                "due_date"        => Carbon::now()->addWeek(),
                "status"          => "PENDING",
                "invoice_address" => $invoiceAddress,
                "user_id"         => $request->user_id
            ]);

            $__total_amount = 0;
            $_total_price = 0;
            $_total_vat = 0;
            $_total_price_with_vat = 0;
            foreach ($items['id'] as $key => $product) {
                $amount = $request->product['amount'][$key] ? commaToDot($request->product['amount'][$key]) : null;
                $product_name = $request->product['name'][$key] ?? null;
                $id = $request->product['id'][$key] ?? null;
                $price_id = $request->product['price_id'][$key] ?? null;
                $quantity = $request->product['quantity'][$key] ?? 1;
                $vat_percent = $request->product['vat_percent'][$key] ?? null;

                if (!$amount || !$id || !$price_id) continue;

                $servicePrice = 0; //kdv hariç
                $getAdditionalServices = [];
//                if (isset($item->additional_services) && $item->additional_services) {
//                    foreach ($item->additional_services as $key => $additional_service) {
//                        $serviceData = $item->getAdditionalServices($key, $additional_service);
//                        $getAdditionalServices[] = $serviceData;
//                        $servicePrice += $serviceData["price_without_vat"];
//                    }
//                }

                $priceData = Price::find($price_id)->toArray();
                unset($priceData['deleted_at']);
                unset($priceData['created_at']);
                unset($priceData['updated_at']);
                unset($priceData['product']);


                $priceWithoutVat = $amount / (1 + ($vat_percent / 100));
                $priceData['price_with_vat'] = $amount;
                $priceData['price'] = $priceWithoutVat;
                $priceData['total_vat'] = ($priceWithoutVat * $vat_percent) / 100;

                $_total_price += $priceWithoutVat;
                $_total_price_with_vat += $priceData['price_with_vat'];
                $_total_vat += $priceData['total_vat'];

                $__total_amount += $priceData['price_with_vat'];

                $productData = Product::find($id)->toArray();

                unset($productData['deleted_at']);
                unset($productData['created_at']);
                unset($productData['updated_at']);


                $order = new Order();
                $order->product_data = $productData;
                $order->order_id = Uuid::uuid4()->toString();
                $order->start_date = Carbon::now();
                $order->end_date = addDurationToDate($priceData['duration'], $priceData['duration_unit'], Carbon::now());
                $order->status = 'PENDING';
                $order->product_id = $productData['id'];
                $order->user_id = $user_id;
                $order->save();

                $orderDetailId = OrderDetail::create([
                    "order_id"            => $order->id,
                    "is_active"           => 1,
                    "is_hidden"           => 1,
                    "additional_services" => $getAdditionalServices,
                    "price_data"          => $priceData,
                    "price_id"            => $priceData['id'],
                    "checkout_id"         => null
                ]);

                InvoiceItem::create([
                    "type"                 => "NEW",
                    "name"                 => $productData['name'] . " | " . $priceData['duration'] . " " . __(mb_strtolower($priceData['duration_unit'])),
                    "total_price"          => $priceData['price'],
                    "vat_percent"          => $productData['vat_percent'],
                    "total_price_with_vat" => $priceData['price_with_vat'],
                    "additional_services"  => $getAdditionalServices,
                    "product_id"           => $productData['id'],
                    "price_id"             => $priceData['id'],
                    "order_id"             => $order->id,
                    "order_detail_id"      => $orderDetailId->id,
                    "invoice_id"           => $invoice->id
                ]);
            }

//            $checkout->update(["amount" => $__total_amount]);


            $invoice->update([
                "total_price"          => $_total_price,
                "total_vat"            => $_total_vat,
                "total_price_with_vat" => $_total_price_with_vat,
            ]);

            /* PASTE END*/
            DB::commit();
            return $this->successResponse(__("created_response", ["name" => __("invoice")]), ["redirectUrl" => route("admin.invoices.show", ["invoice" => $invoice->id])]);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage() . ' - ' . $e->getLine());
        }
    }

    public function statusCounts()
    {
        $counts = Invoice::selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status = 'PENDING' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'PAID' THEN 1 ELSE 0 END) as paid,
            SUM(CASE WHEN status = 'CANCELLED' THEN 1 ELSE 0 END) as cancelled,
            SUM(CASE WHEN formalized_at IS NOT NULL THEN 1 ELSE 0 END) as formalized
        ")->first();

        return response()->json($counts);
    }

    public function ajax(Request $request)
    {
        $whereSearch = "invoices.deleted_at IS NULL";
        $showAllList = $request->showAllList;
        if ($showAllList) {
            $searchableColumns = [
                "invoices.id",
                db_user_full_name_expr('users'),
                "invoices.invoice_date",
                "invoices.total_price_with_vat",
                "invoices.status",
            ];
        } else {
            $searchableColumns = [
                "invoices.id",
                "invoices.invoice_date",
                "invoices.total_price_with_vat",
                "invoices.status",
            ];
            $userId = $request->userId;
            if ($userId) {
                $whereSearch .= " AND invoices.user_id = {$userId} ";
            }
        }

        if (isset($request->order[0]["column"]) and isset($request->order[0]["dir"])) {
            $orderBy = $searchableColumns[$request->order[0]["column"]] . " " . $request->order[0]["dir"];
        } else {
            $orderBy = "invoices.id DESC";
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
            if ($status === 'FORMALIZED') {
                $whereSearch .= " AND invoices.formalized_at IS NOT NULL";
            } else {
                $safeStatus = preg_replace('/[^A-Z_]/', '', $status);
                $whereSearch .= " AND invoices.status = '{$safeStatus}'";
            }
        }

        $start = $request->start ?? 0;
        $length = $request->length == -1 ? 10 : $request->length;

        $query = Invoice::select(
            'invoices.*',
            DB::raw(db_user_full_name_expr('users').' as user_name'),
        )
            ->leftJoin('users', 'users.id', '=', 'invoices.user_id')
            ->whereRaw($whereSearch)
            ->orderByRaw($orderBy);
        $countFilteredRecords = $query->count();
        $query = $query->skip($start)->take($length);

        $list = $query->get();
        $countTotalRecords = $query->count();

        $data = [];
        foreach ($list as $item) {
            $checkbox = '<div class="form-check form-check-sm form-check-custom form-check-solid"><input class="form-check-input bulk-check" type="checkbox" value="' . $item->id . '" /></div>';
            if ($showAllList) {
                $data[] = [
                    $checkbox,
                    "<span data-id='" . $item->id . "' class='badge badge-sm badge-light-primary'>#" . $item->invoice_number . "</span>",
                    "<a href='" . route("admin.users.show", ["user" => $item->user_id]) . "'>" . $item->user_name . "</a>",
                    "<span class='badge badge-secondary'>" . convertDate($item->invoice_date) . "</span>",
                    "<span class='badge badge-secondary badge-lg'>" . showBalance($item->total_price_with_vat, true) . "</span>",
                    $item->drawStatus(),
                    "<a href='" . route("admin.invoices.show", ["invoice" => $item->id]) . "' class='btn btn-light-primary btn-sm'>" . __("view") . "</a>"
                ];
            } else {
                $data[] = [
                    $checkbox,
                    "<span data-id='" . $item->id . "' class='badge badge-sm badge-light-primary'>#" . $item->invoice_number . "</span>",
                    "<span class='badge badge-secondary'>" . convertDate($item->invoice_date) . "</span>",
                    "<span class='badge badge-secondary badge-lg'>" . showBalance($item->total_price_with_vat, true) . "</span>",
                    $item->drawStatus(),
                    "<a href='" . route("admin.invoices.show", ["invoice" => $item->id]) . "' class='btn btn-light-primary btn-sm'>" . __("view") . "</a>"
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

    public function bulkAction(Request $request)
    {
        $ids = $request->input('ids', []);
        $action = $request->input('action');

        if (empty($ids) || !$action) {
            return response()->json(['success' => false, 'message' => 'Geçersiz istek.']);
        }

        $invoices = Invoice::whereIn('id', $ids)->get();

        if ($invoices->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Fatura bulunamadı.']);
        }

        $count = 0;
        switch ($action) {
            case 'mark_paid':
                foreach ($invoices as $invoice) {
                    if ($invoice->status !== 'PAID') {
                        $invoice->update(['status' => 'PAID']);
                        $count++;
                    }
                }
                return response()->json(['success' => true, 'message' => "{$count} fatura ödendi olarak işaretlendi."]);

            case 'mark_pending':
                foreach ($invoices as $invoice) {
                    if ($invoice->status !== 'PENDING') {
                        $invoice->update(['status' => 'PENDING']);
                        $count++;
                    }
                }
                return response()->json(['success' => true, 'message' => "{$count} fatura bekliyor olarak işaretlendi."]);

            case 'mark_cancelled':
                foreach ($invoices as $invoice) {
                    if ($invoice->status !== 'CANCELLED') {
                        $invoice->update(['status' => 'CANCELLED']);
                        $count++;
                    }
                }
                return response()->json(['success' => true, 'message' => "{$count} fatura iptal edildi."]);

            case 'delete':
                $count = count($ids);
                Invoice::whereIn('id', $ids)->delete();
                return response()->json(['success' => true, 'message' => "{$count} fatura silindi."]);

            default:
                return response()->json(['success' => false, 'message' => 'Geçersiz işlem.']);
        }
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(["items"]);
        return view("admin.pages.invoices.details.index", compact("invoice"));
    }

    public function update(UpdateRequest $request, Invoice $invoice)
    {
        DB::beginTransaction();
        try {
            $invoiceData = [
                "invoice_date" => convertDate($request->invoice_date)
            ];
            $invoiceAddress = $request->invoice_address;
            if ($invoiceAddress) {
                $taxNumber = $invoiceAddress["invoice_type"] == "CORPORATE" ? ($invoiceAddress["tax_number"] ?? null) : ($invoiceAddress["identity_number"] ?? null);
                $invoiceData["invoice_address"] = [
                    "invoice_type" => $invoiceAddress["invoice_type"] ?? null,
                    "address"      => $invoiceAddress["address"] ?? null,
                    "district"     => [
                        "id"    => $invoiceAddress["district"]["id"] ?? null,
                        "title" => $invoiceAddress["district"]["title"] ?? null
                    ],
                    "city"         => [
                        "id"    => $invoiceAddress["city"]["id"] ?? null,
                        "title" => $invoiceAddress["city"]["title"] ?? null
                    ],
                    "country"      => [
                        "id"    => $invoiceAddress["country"]["id"] ?? null,
                        "title" => $invoiceAddress["country"]["title"] ?? null
                    ],
                    "tax_number"   => $taxNumber,
                    "tax_office"   => $invoiceAddress["tax_office"] ?? null,
                    "company_name" => $invoiceAddress["company_name"] ?? null,
                ];
            }
            $invoice->fill($invoiceData)->save();
            if ($invoice->status == "PAID") {
                DB::commit();
                return $this->successResponse("Değişiklikler başarıyla kaydedildi. Ödemesi alınmış faturalarda tutar bilgileri güncellenemez.");
            }
            $invoiceItems = $request->input('invoice_item', []);

            $__total_price = 0;
            $__total_vat = 0;
            $__total_price_with_vat = 0;
            foreach ($invoiceItems["id"] as $key => $invoiceItemId) {

                $invoiceItem = InvoiceItem::find($invoiceItemId);
                if (!$invoiceItem) continue;


                $price = commaToDot($invoiceItems["price"][$key]);
                $vatPercent = $invoiceItems["vat_percent"][$key];
                $amount = commaToDot($invoiceItems["amount"][$key]);
                $priceData = [];
                if ($invoiceItem->type != 'CUSTOM'){
                    $priceData = $invoiceItem->orderDetail->price_data;
                }

                $priceData["price"] = $price;
                $priceData["price_without_vat"] = $price;
                $priceData["draw_price"] = showBalance($amount);
                $priceData["total_vat"] = $price * $vatPercent / 100;
                $priceData["price_with_vat"] = $amount;

                $invoiceItem->update([
                    "total_price"          => $priceData["price"],
                    "vat_percent"          => $vatPercent,
                    "total_price_with_vat" => $priceData["price_with_vat"]
                ]);

                if ($invoiceItem->type != 'CUSTOM'){
                    $invoiceItem->orderDetail()->update([
                        "price_data" => $priceData
                    ]);
                }


                $__total_price += $priceData["price"];
                $__total_vat += $priceData["total_vat"];
                $__total_price_with_vat += $priceData["price_with_vat"];
            }

            $invoice->fill([
                "total_price"          => $__total_price,
                "total_vat"            => $__total_vat,
                "total_price_with_vat" => $__total_price_with_vat
            ])->save();

            DB::commit();
            return $this->successResponse(__("edited_response", ["name" => __("invoice")]));
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage());
        }
    }

    public function delete(Invoice $invoice)
    {
        if ($invoice->delete()) {
            return $this->successResponse(__("deleted_response", ["name" => __("invoice")]), ["redirectUrl" => route("admin.invoices.index")]);
        }
        return $this->errorResponse();
    }

    public function formalize(Invoice $invoice, EInvoiceManager $EInvoiceManager)
    {
        $formalize = $EInvoiceManager->formalizeInvoice($invoice);

        if ($invoice->user) {
            \App\Services\NotificationTemplateService::send('invoice_formalized', $invoice->user, [
                'fatura_no' => $invoice->invoice_number ?? $invoice->id,
                'tutar' => number_format($invoice->total_price_with_vat ?? 0, 2, ',', '.'),
                'fatura_url' => url('/invoices/' . $invoice->id),
            ]);
        }

        return $formalize;
        return $this->errorResponse('Fatura resmileştirilirken bir sorun oluştu.');
    }

    public function togglePaymentStatus(Request $request, Invoice $invoice)
    {
        $newStatus = $request->input('status');

        if (!in_array($newStatus, ['PAID', 'PENDING', 'CANCELLED'])) {
            return $this->errorResponse('Geçersiz durum.');
        }

        $invoice->update(['status' => $newStatus]);

        if ($invoice->user) {
            $vars = [
                'fatura_no' => $invoice->invoice_number ?? $invoice->id,
                'tutar' => number_format($invoice->total_price_with_vat ?? 0, 2, ',', '.'),
                'fatura_url' => url('/invoices/' . $invoice->id),
            ];
            if ($newStatus === 'CANCELLED') {
                \App\Services\NotificationTemplateService::send('invoice_cancelled', $invoice->user, $vars);
            } elseif ($newStatus === 'PAID') {
                \App\Services\NotificationTemplateService::send('invoice_paid', $invoice->user, $vars);
            }
        }

        $statusLabels = [
            'PAID' => 'Ödendi',
            'PENDING' => 'Ödenmedi',
            'CANCELLED' => 'İptal Edildi',
        ];

        return $this->successResponse('Fatura durumu "' . ($statusLabels[$newStatus] ?? $newStatus) . '" olarak güncellendi.');
    }

    public function showPdf(Invoice $invoice)
    {
        if (Storage::exists($invoice->invoice_pdf))
            return Storage::response($invoice->invoice_pdf);

        return die('PDF Bulunamadı.');

    }
}
