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

        // Browser-mode (no JSON requested): redirect to product page on error/success
        $expectsJson = $request->expectsJson() || $request->input("format") === "json";

        if (isset($result["error"])) {
            if ($expectsJson) return $this->errorResponse($result["error"]);
            // For browser flow, redirect to the dashboard/home with error flash
            return redirect()->route("portal.auth.login")->with("error", $result["error"]);
        }

        $product = $result["product"];
        $price = $result["price"];

        if ($expectsJson) {
            return $this->successResponse(__("success"), [
                "redirect_url" => route("portal.products.show", ["product" => $product->id]),
                "add_to_basket_url" => route("portal.basket.addToBasket", ["price" => $price->id]),
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

        // Browser mode: add to basket directly via service, then redirect
        try {
            $this->addItemToBasket($price);
        } catch (\Throwable $e) {
            \Log::warning("AUTO_PURCHASE_BASKET_ADD_FAIL", ["err" => $e->getMessage()]);
        }

        return redirect()->route("portal.basket.index");
    }

    /**
     * Add an item to the current basket (auth user or guest session).
     */
    protected function addItemToBasket(\App\Models\Price $price): void
    {
        $sid = session()->getId();
        if (\Illuminate\Support\Facades\Auth::check()) {
            $basket = \App\Models\Basket::firstOrCreate(["user_id" => \Illuminate\Support\Facades\Auth::id()]);
        } else {
            $basket = \App\Models\Basket::firstOrCreate([
                "session_id" => $sid,
                "user_id" => null,
            ]);
        }
        // Avoid duplicate
        $exists = $basket->items()->where("price_id", $price->id)->exists();
        if ($exists) return;
        \App\Models\BasketItem::create([
            "basket_id" => $basket->id,
            "product_id" => $price->product_id,
            "price_id" => $price->id,
            "is_test_product" => 0,
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
