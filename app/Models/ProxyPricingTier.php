<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProxyPricingTier extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    protected $casts = [
        "is_active" => "boolean",
        "price_per_unit" => "decimal:2",
    ];

    public function proxyType(): BelongsTo
    {
        return $this->belongsTo(ProxyTypeSetting::class, "proxy_type_id");
    }
}
