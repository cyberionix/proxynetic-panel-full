<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProxyTypeSetting;
use App\Models\ProxyPricingTier;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Traits\AjaxResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProxyTypeController extends Controller
{
    use AjaxResponses;

    public function index()
    {
        $types = ProxyTypeSetting::with("tiers", "category")->orderBy("display_name")->get();
        $categories = ProductCategory::orderBy("name")->get();
        $deliveryTypes = ["LOCALTONET","STACK","LOCALTONETV4","THREEPROXY","LOCALTONET_ROTATING","PPROXY","PPROXYU"];
        return view("admin.pages.proxy-types.index", compact("types","categories","deliveryTypes"));
    }

    public function update(Request $request, ProxyTypeSetting $type)
    {
        $request->validate([
            "max_quantity" => "required|integer|min:1",
            "delivery_type" => "required|string",
            "category_id" => "nullable|exists:product_categories,id",
            "is_active" => "nullable",
        ]);

        DB::beginTransaction();
        try {
            $type->update([
                "max_quantity" => $request->max_quantity,
                "delivery_type" => $request->delivery_type,
                "category_id" => $request->category_id,
                "delivery_items_template" => $request->input("delivery_items_template_json")
                    ? json_decode($request->input("delivery_items_template_json"), true)
                    : $type->delivery_items_template,
                "default_properties" => $request->input("default_properties"),
                "is_active" => $request->boolean("is_active"),
                "quantity_unit" => $request->input("quantity_unit", $type->quantity_unit),
            ]);

            // Update tiers if provided
            if ($request->has("tiers")) {
                foreach ($request->input("tiers", []) as $tierId => $tierData) {
                    if (isset($tierData["delete"]) && $tierData["delete"]) {
                        ProxyPricingTier::whereKey($tierId)->delete();
                        continue;
                    }
                    $payload = [
                        "duration_days" => (int)($tierData["duration_days"] ?? 30),
                        "min_quantity" => (int)($tierData["min_quantity"] ?? 1),
                        "max_quantity" => (int)($tierData["max_quantity"] ?? 9),
                        "price_per_unit" => (float)str_replace(",", ".", $tierData["price_per_unit"] ?? 0),
                        "is_active" => !empty($tierData["is_active"]),
                    ];
                    if (str_starts_with((string)$tierId, "new_")) {
                        $payload["proxy_type_id"] = $type->id;
                        ProxyPricingTier::create($payload);
                    } else {
                        ProxyPricingTier::whereKey($tierId)->update($payload);
                    }
                }
            }

            DB::commit();
            return $this->successResponse(__("success"));
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->errorResponse("Kayıt başarısız: " . $e->getMessage());
        }
    }

    public function autoProductsList()
    {
        $products = Product::where("is_auto_generated", 1)
            ->with("prices", "category")
            ->orderByDesc("id")
            ->paginate(20);
        return view("admin.pages.proxy-types.auto-products", compact("products"));
    }
}
