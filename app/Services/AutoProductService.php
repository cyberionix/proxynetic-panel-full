<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Price;
use App\Models\ProxyTypeSetting;
use App\Models\Currency;
use Illuminate\Support\Facades\DB;

class AutoProductService
{
    /**
     * Find or create an auto-generated product for the given proxy type, quantity, duration.
     * Returns ['product' => Product, 'price' => Price, 'tier' => ProxyPricingTier, 'total' => float]
     * Or ['error' => 'message'] on failure.
     */
    public function findOrCreateForOrder(string $typeCode, int $quantity, int $durationDays = 30): array
    {
        $setting = ProxyTypeSetting::where("type_code", $typeCode)->where("is_active", 1)->first();
        if (!$setting) return ["error" => "Geçersiz proxy türü: $typeCode"];

        if ($quantity < 1) return ["error" => "Minimum 1 adet seçmelisiniz."];
        if ($quantity > $setting->max_quantity) {
            return ["error" => "Bu üründen maksimum {$setting->max_quantity} {$setting->quantity_unit} alabilirsiniz."];
        }

        $tier = $setting->findTier($quantity, $durationDays);
        if (!$tier) {
            return ["error" => "Bu adet ve süre için fiyat bulunamadı."];
        }

        $totalPrice = round($quantity * (float)$tier->price_per_unit, 2);

        // Build a deterministic name so we find existing
        $unitLabel = $setting->quantity_unit === "GB" ? "GB" : "Adet";
        $name = "{$quantity} {$unitLabel} {$setting->display_name} ({$durationDays} Gün)";

        // Try to find existing auto-generated product matching exactly
        $product = Product::where("is_auto_generated", 1)
            ->where("name", $name)
            ->first();

        $price = null;

        if (!$product) {
            DB::beginTransaction();
            try {
                $product = Product::create([
                    "name" => $name,
                    "category_id" => $setting->category_id,
                    "delivery_type" => $setting->delivery_type,
                    "delivery_count" => $quantity,
                    "delivery_items" => $setting->delivery_items_template,
                    "attrs" => $setting->default_attrs ?? [],
                    "properties" => $setting->default_properties,
                    "is_active" => 1,
                    "is_link_only" => 1,
                    "is_auto_generated" => 1,
                    "auto_meta" => [
                        "type_code" => $typeCode,
                        "quantity" => $quantity,
                        "duration_days" => $durationDays,
                        "unit" => $setting->quantity_unit,
                        "tier_id" => $tier->id,
                        "tier_price_per_unit" => (float)$tier->price_per_unit,
                    ],
                    "vat_percent" => 0,
                ]);

                // Map duration_days to (duration, duration_unit)
                $duration = $durationDays;
                $unit = "DAILY";
                if ($durationDays === 30) { $duration = 1; $unit = "MONTHLY"; }
                elseif ($durationDays === 7) { $duration = 1; $unit = "WEEKLY"; }
                elseif ($durationDays === 1) { $duration = 1; $unit = "DAILY"; }

                $price = Price::create([
                    "product_id" => $product->id,
                    "duration" => $duration,
                    "duration_unit" => $unit,
                    "price" => $totalPrice,
                    "currency_id" => Currency::DEFAULT_ID ?? 1,
                    "is_test_product" => 0,
                ]);

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                \Log::error("AUTO_PRODUCT_FAIL", ["err" => $e->getMessage(), "type" => $typeCode, "qty" => $quantity]);
                return ["error" => "Ürün oluşturulamadı: " . $e->getMessage()];
            }
        } else {
            $price = $product->prices()->first();
        }

        return [
            "product" => $product,
            "price" => $price,
            "tier" => $tier,
            "total" => $totalPrice,
            "setting" => $setting,
        ];
    }
}
