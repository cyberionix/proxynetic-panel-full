<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Services\AutoProductService;
use App\Traits\AjaxResponses;
use Illuminate\Http\Request;

class PublicProxyController extends Controller
{
    use AjaxResponses;

    public function autoPurchase(Request $request, AutoProductService $svc)
    {
        $request->validate([
            "type" => "required|string|max:32",
            "quantity" => "required|integer|min:1|max:1000",
            "duration_days" => "nullable|integer|min:1|max:365",
        ]);

        $type = strtoupper($request->input("type"));
        $qty = (int)$request->input("quantity");
        $duration = (int)$request->input("duration_days", 30);

        $result = $svc->findOrCreateForOrder($type, $qty, $duration);
        if (isset($result["error"])) {
            return $this->errorResponse($result["error"]);
        }

        $product = $result["product"];
        $price = $result["price"];

        // Add to current basket via BasketController#addToBasket logic — simplest is redirect to addToBasket route
        $addUrl = route("portal.basket.addToBasket", ["price" => $price->id]);

        return $this->successResponse(__("success"), [
            "redirect_url" => route("portal.products.show", ["product" => $product->id]),
            "add_to_basket_url" => $addUrl,
            "product_id" => $product->id,
            "price_id" => $price->id,
            "total" => $result["total"],
            "tier" => [
                "min" => $result["tier"]->min_quantity,
                "max" => $result["tier"]->max_quantity,
                "per_unit" => (float)$result["tier"]->price_per_unit,
            ],
        ]);
    }

    public function priceQuote(Request $request, AutoProductService $svc)
    {
        $request->validate([
            "type" => "required|string|max:32",
            "quantity" => "required|integer|min:1|max:1000",
            "duration_days" => "nullable|integer|min:1|max:365",
        ]);
        $type = strtoupper($request->input("type"));
        $qty = (int)$request->input("quantity");
        $duration = (int)$request->input("duration_days", 30);

        $setting = \App\Models\ProxyTypeSetting::where("type_code", $type)->where("is_active",1)->first();
        if (!$setting) return $this->errorResponse("Geçersiz proxy türü.");
        if ($qty > $setting->max_quantity) return $this->errorResponse("Maksimum {$setting->max_quantity} {$setting->quantity_unit}.");

        $tier = $setting->findTier($qty, $duration);
        if (!$tier) return $this->errorResponse("Bu adet/süre için fiyat yok.");

        return $this->successResponse(null, [
            "type_code" => $type,
            "display_name" => $setting->display_name,
            "quantity" => $qty,
            "duration_days" => $duration,
            "per_unit" => (float)$tier->price_per_unit,
            "total" => round($qty * (float)$tier->price_per_unit, 2),
            "max_quantity" => $setting->max_quantity,
            "quantity_unit" => $setting->quantity_unit,
        ]);
    }
}
