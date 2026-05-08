<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProxyTypeSetting extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    protected $casts = [
        "delivery_items_template" => "json",
        "default_attrs" => "json",
        "is_active" => "boolean",
    ];

    public function tiers(): HasMany
    {
        return $this->hasMany(ProxyPricingTier::class, "proxy_type_id");
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, "category_id");
    }

    public function calculatePrice(int $quantity, int $durationDays = 30): ?float
    {
        $tier = $this->tiers()
            ->where("duration_days", $durationDays)
            ->where("min_quantity", "<=", $quantity)
            ->where("max_quantity", ">=", $quantity)
            ->where("is_active", 1)
            ->first();
        if (!$tier) return null;
        return round($quantity * (float)$tier->price_per_unit, 2);
    }

    public function findTier(int $quantity, int $durationDays = 30): ?ProxyPricingTier
    {
        return $this->tiers()
            ->where("duration_days", $durationDays)
            ->where("min_quantity", "<=", $quantity)
            ->where("max_quantity", ">=", $quantity)
            ->where("is_active", 1)
            ->first();
    }
}
