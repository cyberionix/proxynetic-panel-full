<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Product\StoreRequest;
use App\Http\Requests\Admin\Product\UpdateRequest;
use App\Models\BasketItem;
use App\Models\Currency;
use App\Models\Order;
use App\Models\Price;
use App\Models\Product;
use App\Models\TokenPool;
use App\Traits\AjaxResponses;
use Google\Service\AIPlatformNotebooks\DataDisk;
use Google\Service\Spanner\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    use AjaxResponses;

    public function index()
    {
        $bookCategories = [];
        return view("admin.pages.products.index", compact("bookCategories"));
    }

    public function ajax(Request $request)
    {
        $searchableColumns = [
            "products.id",
            "products.name",
            "products.name",
            "book_categories.name"
        ];

        $whereSearch = "products.deleted_at IS NULL";

        if (isset($request->order[0]["column"]) and isset($request->order[0]["dir"])) {
            $orderBy = $searchableColumns[$request->order[0]["column"]] . " " . $request->order[0]["dir"];
        } else {
            $orderBy = "products.id DESC";
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

        $query = Product::select('products.*')
            ->whereRaw($whereSearch)
            ->orderByRaw($orderBy);
        $countFilteredRecords = $query->count();
        $query = $query->skip($start)->take($length);

        $list = $query->get();
        $countTotalRecords = $query->count();

        $test_products = Product::testProducts();
        $data = [];
        foreach ($list as $item) {

            if ($test_products->contains('id',$item->id)){
                $test_html = '<i class="fs-1 fa fa-check-circle text-success"></i>';
            }else{
                $test_html = '<i class="fs-1 fa fa-times-circle text-danger"></i>';
            }

            $data[] = [
                "<span data-id='" . $item->id . "'>" . $item->id . "</span>",
                $item->name,
                $test_html,
                '<a href="#" class="btn btn-sm btn-light btn-flex btn-center btn-active-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">' . __("actions") . '
                                        <i class="ki-duotone ki-down fs-5 ms-1"></i></a>
                                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                                        <div class="menu-item px-3">
                                            <a href="' . route('admin.products.edit', ['product' => $item->id]) . '" class="menu-link px-3 editBtn">' . __("edit") . '</a>
                                        </div>
                                        <div class="menu-item px-3">
                                            <a href="javascript:void(0);" class="menu-link px-3 deleteBtn">' . __("delete") . '</a>
                                        </div>
                                    </div>
                                   ',
            ];
        }

        $response = array(
            'recordsTotal' => $countTotalRecords,
            'recordsFiltered' => $countFilteredRecords,
            'data' => $data
        );
        echo json_encode($response);
    }

    public function create()
    {
        $pageTitle = __('create_:name', ['name' => __('product')]);

        $tokenPools = TokenPool::all();
        return view("admin.pages.products.create_update.index", compact(["pageTitle","tokenPools"]));
    }

    public function store(StoreRequest $request)
    {
        DB::beginTransaction();
        try {
            $productData = $request->only(["product.name", "product.properties", "product.category_id", "product.delivery_type"]);
            $productData["product"]["is_active"] = $request->product["status"] == 1 ? 1 : 0;

            $productData["product"]["delivery_items"] = [];
            if ($productData["product"]["delivery_type"] == "STACK") {
                $productData["product"]["delivery_items"] = [
                    "proxies" => $request->product['delivery_items'] ? explode("\r\n", $request->product['delivery_items']) : [],
                    "delivery_count" => $request->delivery_count
                ];
            } else if ($productData["product"]["delivery_type"] == "LOCALTONET") {
                if (!$request->product["token_pool_id"]) return $this->errorResponse("Token havuzu seçimi zorunludur.");
                $productData["product"]["delivery_items"] = [
                    "token_pool_id" => $request->product["token_pool_id"],
                    "bandwidth_limit" => [
                        "data_size" => $request->data_size,
                        "data_size_type" => $request->data_size_type,
                    ],
                ];
            } else if ($productData["product"]["delivery_type"] == "LOCALTONETV4") {
                $v4 = $this->buildLocaltonetV4DeliveryItems($request);
                if (isset($v4['error'])) {
                    return $this->errorResponse($v4['error']);
                }
                $productData["product"]["delivery_items"] = $v4['items'];
            } else if ($productData["product"]["delivery_type"] == "THREEPROXY") {
                $tp = $this->buildThreeProxyDeliveryItems($request);
                if (isset($tp['error'])) {
                    return $this->errorResponse($tp['error']);
                }
                $productData["product"]["delivery_items"] = $tp['items'];
            } else if ($productData["product"]["delivery_type"] == "LOCALTONET_ROTATING") {
                $lr = $this->buildLocaltonetRotatingDeliveryItems($request);
                if (isset($lr['error'])) {
                    return $this->errorResponse($lr['error']);
                }
                $productData["product"]["delivery_items"] = $lr['items'];
            }

            $productData["product"]["vat_percent"] = 20;
            $product = Product::create($productData["product"]);

            $file = $request->file('isp_image');
            if ($file) {
                $ispImageName = $product->id . " - " . $file->getClientOriginalName();
                Storage::disk("public")->putFileAs("assets/uploads/isp_images", $file, $ispImageName);
                $product->update(["isp_image" => "uploads/isp_images/" . $ispImageName]);
            }

            /** start::Pricing */
            $pricing = $request->pricing;
            $countPrice = 0;
            foreach ($pricing["new"]["duration"] as $key => $i) {
                if (!trim($pricing["new"]["duration_unit"][$key])) continue;
                if ($pricing["new"]["duration_unit"][$key] != "ONE_TIME" && !trim($pricing["new"]["duration"][$key])) continue;

                $price = $pricing["new"]["price"][$key] ? commaToDot($pricing["new"]["price"][$key]) : 0;

                $item = [
                    "duration" => $pricing["new"]["duration_unit"][$key] == "ONE_TIME" ? null : $pricing["new"]["duration"][$key],
                    "duration_unit" => $pricing["new"]["duration_unit"][$key],
                    "price" => $price,
                    "currency_id" => Currency::DEFAULT_ID,
                    "product_id" => $product->id
                ];

                Price::create($item);
                $countPrice++;
            }

            if ($countPrice == 0){
                return $this->errorResponse("En az bir adet fiyat eklemelisiniz.");
            }
            /** end::Pricing */

            /** start::Additional Services */
            $serviceType = $request->service_type;
            $newProxyTypeAttr = null;
            if (isset($request->service_type["protocol_select"]["status"]) && $request->service_type["protocol_select"]["status"] == 1){
                $newProxyTypeAttr = getProxyTypeForAttrs();

                $newProxyTypeAttr["options"][0]["price"] =  $serviceType["protocol_select"]["price"][0] ? commaToDot($serviceType["protocol_select"]["price"][0]) : "0.00";
                $newProxyTypeAttr["options"][1]["price"] =  $serviceType["protocol_select"]["price"][1] ? commaToDot($serviceType["protocol_select"]["price"][1]) : "0.00";
            }

            $newQuotaAttr = null;
            if (isset($request->service_type["quota"]["status"]) && $request->service_type["quota"]["status"] == 1){
                $quotaOptions = [];
                foreach ($serviceType["quota"]["label"] as $key => $label){
                    if (!trim($label) ||!trim($serviceType["quota"]["price"][$key])) continue;
                    $quotaOptions[] = [
                        "label" => $label,
                        "value" => $serviceType["quota"]["value"][$key] ?? 0,
                        "price" => commaToDot($serviceType["quota"]["price"][$key])
                    ];
                }

                $newQuotaAttr = getQuotaForAttrs();
                $newQuotaAttr["options"] = $quotaOptions;

                if (!$quotaOptions){
                    $newQuotaAttr = null;
                }
            }

            $newQuotaDurationAttr = null;
            if (isset($request->service_type["quota_duration"]["status"]) && $request->service_type["quota_duration"]["status"] == 1){
                $quotaDurationOptions = [];
                foreach ($serviceType["quota_duration"]["label"] as $key => $label){
                    if (!trim($label) || !trim($serviceType["quota_duration"]["duration"][$key]) || !trim($serviceType["quota_duration"]["duration_unit"][$key]) || !trim($serviceType["quota_duration"]["price"][$key])) continue;
                    $quotaDurationOptions[] = [
                        "label" => $label,
                        "value" => Str::slug($label, "_"),
                        "gb" => $serviceType["quota_duration"]["gb"][$key] ?? 0,
                        "duration" => $serviceType["quota_duration"]["duration_unit"][$key] == "ONE_TIME" ? null : $serviceType["quota_duration"]["duration"][$key],
                        "duration_unit" => $serviceType["quota_duration"]["duration_unit"][$key],
                        "price" => commaToDot($serviceType["quota_duration"]["price"][$key])
                    ];
                }

                $newQuotaDurationAttr = getQuotaDurationForAttrs();
                $newQuotaDurationAttr["options"] = $quotaDurationOptions;

                if (!$quotaDurationOptions){
                    $newQuotaDurationAttr = null;
                }
            }

            $newTpExtraDurationAttr = null;
            if (isset($serviceType["tp_extra_duration"]["status"]) && $serviceType["tp_extra_duration"]["status"] == 1) {
                $opts = [];
                if (isset($serviceType["tp_extra_duration"]["label"])) {
                    foreach ($serviceType["tp_extra_duration"]["label"] as $key => $label) {
                        if (!trim($label) || !trim($serviceType["tp_extra_duration"]["price"][$key] ?? '')) continue;
                        $opts[] = [
                            "label" => $label,
                            "value" => Str::slug($label, "_"),
                            "duration" => $serviceType["tp_extra_duration"]["duration"][$key] ?? 1,
                            "duration_unit" => $serviceType["tp_extra_duration"]["duration_unit"][$key] ?? "MONTHLY",
                            "price" => commaToDot($serviceType["tp_extra_duration"]["price"][$key]),
                        ];
                    }
                }
                if (count($opts) > 0) {
                    $newTpExtraDurationAttr = getTpExtraDurationForAttrs();
                    $newTpExtraDurationAttr["options"] = $opts;
                }
            }

            $newTpChangeIpsAttr = null;
            if (isset($serviceType["tp_change_ips"]["status"]) && $serviceType["tp_change_ips"]["status"] == 1) {
                $newTpChangeIpsAttr = getTpChangeIpsForAttrs();
                $newTpChangeIpsAttr["price"] = commaToDot($serviceType["tp_change_ips"]["price"] ?? "0");
            }

            $newTpSubnetIpsAttr = null;
            if (isset($serviceType["tp_subnet_ips"]["status"]) && $serviceType["tp_subnet_ips"]["status"] == 1) {
                $newTpSubnetIpsAttr = getTpSubnetIpsForAttrs();
                $newTpSubnetIpsAttr["price"] = commaToDot($serviceType["tp_subnet_ips"]["price"] ?? "0");
            }

            $newTpClassIpsAttr = null;
            if (isset($serviceType["tp_class_ips"]["status"]) && $serviceType["tp_class_ips"]["status"] == 1) {
                $newTpClassIpsAttr = getTpClassIpsForAttrs();
                $newTpClassIpsAttr["price"] = commaToDot($serviceType["tp_class_ips"]["price"] ?? "0");
            }

            $newProductAttrs = [];
            if ($newProxyTypeAttr) $newProductAttrs[] = $newProxyTypeAttr;
            if ($newQuotaAttr) $newProductAttrs[] = $newQuotaAttr;
            if ($newQuotaDurationAttr) $newProductAttrs[] = $newQuotaDurationAttr;
            if ($newTpExtraDurationAttr) $newProductAttrs[] = $newTpExtraDurationAttr;
            if ($newTpChangeIpsAttr) $newProductAttrs[] = $newTpChangeIpsAttr;
            if ($newTpSubnetIpsAttr) $newProductAttrs[] = $newTpSubnetIpsAttr;
            if ($newTpClassIpsAttr) $newProductAttrs[] = $newTpClassIpsAttr;

            $product->update([
                "attrs" => $newProductAttrs
            ]);
            /** end::Additional Services */

            DB::commit();
            return $this->successResponse(__("created_response", ["name" => __("product")]), ["redirectUrl" => route("admin.products.edit", ['product' => $product->id])]);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage());
        }
    }

    public function find(Product $product)
    {
        return $this->successResponse("", ["data" => $product]);
    }

    public function edit(Product $product)
    {
        $product->load(["category", "prices"]);
        $pageTitle = __('edit_:name', ['name' => __('product')]) . " - " . $product->name;

        $proxyTypeAttr = $product->findAttrsByServiceType("protocol_select");
        $quotaAttr = $product->findAttrsByServiceType("quota");
        $quotaDurationAttr = $product->findAttrsByServiceType("quota_duration");
        $tpExtraDurationAttr = $product->findAttrsByServiceType("tp_extra_duration");
        $tpChangeIpsAttr = collect($product->attrs ?? [])->where("service_type", "tp_change_ips")->first();
        $tpSubnetIpsAttr = collect($product->attrs ?? [])->where("service_type", "tp_subnet_ips")->first();
        $tpClassIpsAttr = collect($product->attrs ?? [])->where("service_type", "tp_class_ips")->first();

        $tokenPools = TokenPool::all();
        return view("admin.pages.products.create_update.index", compact(["pageTitle", "tokenPools","product", "proxyTypeAttr", "quotaAttr", "quotaDurationAttr", "tpExtraDurationAttr", "tpChangeIpsAttr", "tpSubnetIpsAttr", "tpClassIpsAttr"]));
    }

    public function update(UpdateRequest $request, Product $product)
    {
        DB::beginTransaction();
        try {
            $productData = $request->only(["product.name", "product.properties", "product.category_id", "product.delivery_type"]);
            $productData["product"]["is_active"] = $request->product["status"] == 1 ? 1 : 0;

            $productData["product"]["delivery_items"] = [];
            if ($productData["product"]["delivery_type"] == "STACK") {
                $productData["product"]["delivery_items"] = [
                    "proxies" => $request->product['delivery_items'] ? explode("\r\n", $request->product['delivery_items']) : [],
                    "delivery_count" => $request->delivery_count
                ];
            } else if ($productData["product"]["delivery_type"] == "LOCALTONET") {
                if (!isset($request->product["token_pool_id"])) return $this->errorResponse("Teslimat için bir token havuzu seçmelisiniz.");

                $productData["product"]["delivery_items"] = [
                    "token_pool_id" => $request->product["token_pool_id"],
                    "bandwidth_limit" => [
                        "data_size" => $request->data_size,
                        "data_size_type" => $request->data_size_type,
                    ],
                ];
            } else if ($productData["product"]["delivery_type"] == "LOCALTONETV4") {
                $v4 = $this->buildLocaltonetV4DeliveryItems($request);
                if (isset($v4['error'])) {
                    return $this->errorResponse($v4['error']);
                }
                $productData["product"]["delivery_items"] = $v4['items'];
            } else if ($productData["product"]["delivery_type"] == "THREEPROXY") {
                $tp = $this->buildThreeProxyDeliveryItems($request);
                if (isset($tp['error'])) {
                    return $this->errorResponse($tp['error']);
                }
                $productData["product"]["delivery_items"] = $tp['items'];
            } else if ($productData["product"]["delivery_type"] == "LOCALTONET_ROTATING") {
                $lr = $this->buildLocaltonetRotatingDeliveryItems($request);
                if (isset($lr['error'])) {
                    return $this->errorResponse($lr['error']);
                }
                $productData["product"]["delivery_items"] = $lr['items'];
            }

            $product->update($productData['product']);

            /* ISP Image*/
            $file = $request->file('isp_image');

            if ($file) {
                Storage::disk("public")->delete("assets/" . $product->isp_image);
                $ispImageName = $product->id . " - " . $file->getClientOriginalName();
                Storage::disk("public")->putFileAs("assets/uploads/isp_images", $file, $ispImageName);
                $product->update(["isp_image" => "uploads/isp_images/" . $ispImageName]);
            }

            if ($request->isp_image_remove == 1){
                Storage::disk("public")->delete("assets/" . $product->isp_image);
                $product->update(["isp_image" => null]);
            }
            /* ISP Image*/

            /** start::Pricing */
            $pricing = $request->pricing;
            $countPrice = 0;

            if (isset($pricing["old"])){
                foreach ($pricing["old"]["price_id"] as $key => $i) {
                    $findPrice = Price::find($pricing["old"]["price_id"][$key]);
                    if (!$findPrice || !trim($pricing["old"]["duration"][$key]) || !trim($pricing["old"]["duration_unit"][$key])) continue;

                    $price = $pricing["old"]["price"][$key] ? commaToDot($pricing["old"]["price"][$key]) : 0;


                    $findPrice->update([
                        "duration" => $pricing["old"]["duration"][$key],
                        "duration_unit" => $pricing["old"]["duration_unit"][$key],
                        "price" => $price,
                        "currency_id" => Currency::DEFAULT_ID,
                        "product_id" => $product->id,
                        'is_test_product' => isset($pricing['old']['is_test_product']) && isset($pricing['old']['is_test_product'][$findPrice->id]) ? 1 : 0
                    ]);
                    $countPrice++;
                }
                $oldPriceIds = $pricing["old"]["price_id"];

                BasketItem::whereProductId($product->id)->delete();
                Price::whereProductId($product->id)->whereNotIn('id', $oldPriceIds)->delete();
            }

            foreach ($pricing["new"]["duration"] as $key => $i) {
                if (!trim($pricing["new"]["duration_unit"][$key])) continue;
                if ($pricing["new"]["duration_unit"][$key] != "ONE_TIME" && !trim($pricing["new"]["duration"][$key])) continue;

                $price = $pricing["new"]["price"][$key] ? commaToDot($pricing["new"]["price"][$key]) : 0;
                $item = [
                    "duration" => $pricing["new"]["duration_unit"][$key] == "ONE_TIME" ? null : $pricing["new"]["duration"][$key],
                    "duration_unit" => $pricing["new"]["duration_unit"][$key],
                    "price" => $price,
                    "currency_id" => Currency::DEFAULT_ID,
                    "product_id" => $product->id
                ];

                Price::create($item);
                $countPrice++;
            }

            if ($countPrice == 0){
                return $this->errorResponse("En az bir adet fiyat eklemelisiniz.");
            }
            /** end::Pricing */

            /** start::Additional Servies */
            $serviceType = $request->service_type;
            $newProxyTypeAttr = null;
            if (isset($request->service_type["protocol_select"]["status"]) && $request->service_type["protocol_select"]["status"] == 1){
                $newProxyTypeAttr = getProxyTypeForAttrs();

                $newProxyTypeAttr["options"][0]["price"] =  $serviceType["protocol_select"]["price"][0] ? commaToDot($serviceType["protocol_select"]["price"][0]) : "0.00";
                $newProxyTypeAttr["options"][1]["price"] =  $serviceType["protocol_select"]["price"][1] ? commaToDot($serviceType["protocol_select"]["price"][1]) : "0.00";
            }

            $newQuotaAttr = null;
            if (isset($request->service_type["quota"]["status"]) && $request->service_type["quota"]["status"] == 1){
                $quotaOptions = [];
                foreach ($serviceType["quota"]["label"] as $key => $label){
                    if (!trim($label) ||!trim($serviceType["quota"]["price"][$key])) continue;
                    $quotaOptions[] = [
                        "label" => $label,
                        "value" => $serviceType["quota"]["value"][$key] ?? 0,
                        "price" => commaToDot($serviceType["quota"]["price"][$key])
                    ];
                }

                $newQuotaAttr = getQuotaForAttrs();
                $newQuotaAttr["options"] = $quotaOptions;

                if (!$quotaOptions){
                    $newQuotaAttr = null;
                }
            }

            $newQuotaDurationAttr = null;
            if (isset($request->service_type["quota_duration"]["status"]) && $request->service_type["quota_duration"]["status"] == 1){
                $quotaDurationOptions = [];
                foreach ($serviceType["quota_duration"]["label"] as $key => $label){
                    if (!trim($label) || !trim($serviceType["quota_duration"]["duration"][$key]) || !trim($serviceType["quota_duration"]["duration_unit"][$key]) || !trim($serviceType["quota_duration"]["price"][$key])) continue;

                    $quotaDurationOptions[] = [
                        "label" => $label,
                        "value" => Str::slug($label, "_"),
                        "gb" => $serviceType["quota_duration"]["gb"][$key] ?? 0,
                        "duration" => $serviceType["quota_duration"]["duration_unit"][$key] == "ONE_TIME" ? null : $serviceType["quota_duration"]["duration"][$key],
                        "duration_unit" => $serviceType["quota_duration"]["duration_unit"][$key],
                        "price" => commaToDot($serviceType["quota_duration"]["price"][$key])
                    ];
                }

                $newQuotaDurationAttr = getQuotaDurationForAttrs();
                $newQuotaDurationAttr["options"] = $quotaDurationOptions;

                if (!$quotaDurationOptions){
                    $newQuotaDurationAttr = null;
                }
            }

            $newTpExtraDurationAttr2 = null;
            if (isset($serviceType["tp_extra_duration"]["status"]) && $serviceType["tp_extra_duration"]["status"] == 1) {
                $opts2 = [];
                if (isset($serviceType["tp_extra_duration"]["label"])) {
                    foreach ($serviceType["tp_extra_duration"]["label"] as $key => $label) {
                        if (!trim($label) || !trim($serviceType["tp_extra_duration"]["price"][$key] ?? '')) continue;
                        $opts2[] = [
                            "label" => $label,
                            "value" => Str::slug($label, "_"),
                            "duration" => $serviceType["tp_extra_duration"]["duration"][$key] ?? 1,
                            "duration_unit" => $serviceType["tp_extra_duration"]["duration_unit"][$key] ?? "MONTHLY",
                            "price" => commaToDot($serviceType["tp_extra_duration"]["price"][$key]),
                        ];
                    }
                }
                if (count($opts2) > 0) {
                    $newTpExtraDurationAttr2 = getTpExtraDurationForAttrs();
                    $newTpExtraDurationAttr2["options"] = $opts2;
                }
            }

            $newTpChangeIpsAttr2 = null;
            if (isset($serviceType["tp_change_ips"]["status"]) && $serviceType["tp_change_ips"]["status"] == 1) {
                $newTpChangeIpsAttr2 = getTpChangeIpsForAttrs();
                $newTpChangeIpsAttr2["price"] = commaToDot($serviceType["tp_change_ips"]["price"] ?? "0");
            }

            $newTpSubnetIpsAttr2 = null;
            if (isset($serviceType["tp_subnet_ips"]["status"]) && $serviceType["tp_subnet_ips"]["status"] == 1) {
                $newTpSubnetIpsAttr2 = getTpSubnetIpsForAttrs();
                $newTpSubnetIpsAttr2["price"] = commaToDot($serviceType["tp_subnet_ips"]["price"] ?? "0");
            }

            $newTpClassIpsAttr2 = null;
            if (isset($serviceType["tp_class_ips"]["status"]) && $serviceType["tp_class_ips"]["status"] == 1) {
                $newTpClassIpsAttr2 = getTpClassIpsForAttrs();
                $newTpClassIpsAttr2["price"] = commaToDot($serviceType["tp_class_ips"]["price"] ?? "0");
            }

            $newProductAttrs = [];
            if ($newProxyTypeAttr) $newProductAttrs[] = $newProxyTypeAttr;
            if ($newQuotaAttr) $newProductAttrs[] = $newQuotaAttr;
            if ($newQuotaDurationAttr) $newProductAttrs[] = $newQuotaDurationAttr;
            if ($newTpExtraDurationAttr2) $newProductAttrs[] = $newTpExtraDurationAttr2;
            if ($newTpChangeIpsAttr2) $newProductAttrs[] = $newTpChangeIpsAttr2;
            if ($newTpSubnetIpsAttr2) $newProductAttrs[] = $newTpSubnetIpsAttr2;
            if ($newTpClassIpsAttr2) $newProductAttrs[] = $newTpClassIpsAttr2;

            $product->update([
                "attrs" => $newProductAttrs
            ]);
            /** end::Additional Servies */

            /** start::Upgrade */
            $upgradeData = $request->upgrade;

            Price::whereIn("id", $product->prices->pluck("id"))->update(["upgradeable_price_ids" => null]);
            foreach ($product->prices as $price) {
                $id = $price->id;
                $upgradeablePriceIds = @$upgradeData['price_id'][$id] ?? [];
                Price::whereId($id)->update([
                    "upgradeable_price_ids" => $upgradeablePriceIds
                ]);
            }

            //sanırım aktif orderların price_data larındaki ``upgradeable_price_ids`` da güncellemeliyiz
            /** end::Upgrade */
            DB::commit();

            $pendingOrders = Order::whereStatus("PENDING")->get();
            foreach ($pendingOrders as $order){
                if ($order?->activeDetail?->checkout?->status != "COMPLETED") continue;

                $localtonetFamily = ['LOCALTONET', 'LOCALTONETV4'];
                $allSpecialTypes = ['LOCALTONET', 'LOCALTONETV4', 'THREEPROXY', 'LOCALTONET_ROTATING'];
                if ($productData["product"]["delivery_type"] == "STACK" && in_array($order->product->delivery_type, $allSpecialTypes, true)) {
                    continue;
                }
                if (in_array($productData["product"]["delivery_type"], $allSpecialTypes, true) && $order->product->delivery_type == "STACK") {
                    continue;
                }

                $order->approve();
            }

            return $this->successResponse('Ürün bilgileri başarıyla güncellendi.', ["redirectUrl" => route("admin.products.edit", ['product' => $product->id])]);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * @return array{items?: array<string, mixed>, error?: string}
     */
    private function buildLocaltonetV4DeliveryItems(Request $request): array
    {
        $apiKey = trim((string) $request->input('v4_api_key', ''));
        if ($apiKey === '') {
            return ['error' => 'Localtonetv4 için Localtonet API anahtarı zorunludur.'];
        }

        $ipPoolId = $request->input('v4_ip_pool_id');
        if ($ipPoolId) {
            $pool = \App\Models\IpPool::find($ipPoolId);
            if (! $pool) {
                return ['error' => 'Seçilen IP havuzu bulunamadı.'];
            }
            if ($pool->getEntryCount() === 0) {
                return ['error' => 'Seçilen IP havuzunda hiç satır yok. Önce havuzu doldurun.'];
            }
        } else {
            return ['error' => 'IP havuzu seçimi zorunludur.'];
        }

        $apiUrl = trim((string) $request->input('v4_api_url', 'https://localtonet.com/api'));
        if ($apiUrl === '') {
            $apiUrl = 'https://localtonet.com/api';
        }
        $apiUrl = rtrim($apiUrl, '/');

        $serverCode = trim((string) $request->input('v4_server_code', 'app'));
        if ($serverCode === '') {
            $serverCode = 'app';
        }

        $deliveryCount = (int) $request->input('v4_delivery_count', 1);
        if ($deliveryCount < 1) {
            $deliveryCount = 1;
        }
        if ($deliveryCount > 1000) {
            $deliveryCount = 1000;
        }

        $localServerIp = trim((string) $request->input('v4_local_server_ip', ''));
        if ($localServerIp !== '' && ! filter_var($localServerIp, FILTER_VALIDATE_IP)) {
            return ['error' => 'Yerel sunucu IP geçerli bir IPv4/IPv6 adresi olmalıdır (boş bırakılabilir).'];
        }

        $items = [
            'api_url' => $apiUrl,
            'api_key' => $apiKey,
            'server_code' => $serverCode,
            'delivery_count' => $deliveryCount,
            'bandwidth_limit' => [
                'data_size' => $request->data_size,
                'data_size_type' => $request->data_size_type,
            ],
            'ip_pool_id' => (int) $ipPoolId,
        ];
        if ($localServerIp !== '') {
            $items['local_server_ip'] = $localServerIp;
        }

        return [
            'items' => $items,
        ];
    }

    /**
     * @return array{items?: array<string, mixed>, error?: string}
     */
    private function buildThreeProxyDeliveryItems(Request $request): array
    {
        $poolId = $request->input('three_proxy_pool_id');
        if (!$poolId) {
            return ['error' => '3Proxy havuzu seçimi zorunludur.'];
        }

        $pool = \App\Models\ThreeProxyPool::find($poolId);
        if (!$pool) {
            return ['error' => 'Seçilen 3Proxy havuzu bulunamadı.'];
        }

        $deliveryCount = (int) $request->input('tp_delivery_count', 1);
        if ($deliveryCount < 1) $deliveryCount = 1;
        if ($deliveryCount > 1000) $deliveryCount = 1000;

        $userPort = $request->input('tp_user_port');
        $userPort = $userPort ? (int) $userPort : null;

        $items = [
            'three_proxy_pool_id' => (int) $poolId,
            'delivery_count' => $deliveryCount,
        ];

        if ($userPort) {
            $items['user_port'] = $userPort;
        }

        return ['items' => $items];
    }

    /**
     * @return array{items?: array<string, mixed>, error?: string}
     */
    private function buildLocaltonetRotatingDeliveryItems(Request $request): array
    {
        $poolId = $request->input('lr_pool_id');
        if (!$poolId) {
            return ['error' => 'Localtonet Rotating havuzu seçimi zorunludur.'];
        }

        $pool = \App\Models\LocaltonetRotatingPool::find($poolId);
        if (!$pool) {
            return ['error' => 'Seçilen Localtonet Rotating havuzu bulunamadı.'];
        }

        $serverCode = trim((string) $request->input('lr_server_code', 'app'));
        if ($serverCode === '') {
            $serverCode = 'app';
        }

        $deliveryCount = (int) $request->input('lr_delivery_count', 1);
        if ($deliveryCount < 1) $deliveryCount = 1;
        if ($deliveryCount > 1000) $deliveryCount = 1000;

        $host = trim((string) $request->input('lr_host', ''));
        if ($host === '') {
            return ['error' => 'Host domain / Server IP alanı zorunludur.'];
        }

        $port = $request->input('lr_port');
        $port = $port ? (int) $port : null;

        $quotaDataSize = $request->input('lr_quota', '');
        $quotaDataSizeType = $request->input('lr_quota_type', '4');

        $items = [
            'lr_pool_id'     => (int) $poolId,
            'server_code'    => $serverCode,
            'delivery_count' => $deliveryCount,
            'host'           => $host,
            'quota'          => [
                'data_size'      => $quotaDataSize,
                'data_size_type' => $quotaDataSizeType,
            ],
        ];

        if ($port) {
            $items['port'] = $port;
        }

        return ['items' => $items];
    }

    public function destroy(Product $product)
    {
        if ($product->delete()) {
            return $this->successResponse(__("deleted_response", ["name" => __("product")]));
        }

        return $this->errorResponse();
    }

    public function search(Request $request)
    {
        $term = isset($request->term["term"]) ? $request->term["term"] : '';
        $withPassives = isset($request->withPassives) && $request->withPassives == 1 ? 1 : 0;

        $products = Product::where("name", 'LIKE', '%' . $term . '%')
            ->orWhere('id', 'LIKE', '%' . $term . '%');

        if (!$withPassives){
            $products = $products->where('is_active', 1);
        }

        $products = $products->limit(50)
            ->orderByDesc("id")
            ->get();

        $result = [];
        foreach ($products as $product) {
            $name = $product->id . " | " . $product->name;
            if ($withPassives){
                $name .= " " . $product->is_active ? "(Aktif)" : "(Pasif)";
            }
            $result[] = [
                "id" => $product->id,
                "name" => $name,
                "extraParams" => $product
            ];
        }

        return response()->json([
            "items" => $result
        ]);
    }

    public function findPlaylist(Product $product)
    {
        return $this->successResponse("", ["data" => $product->playlists]);
    }
}
