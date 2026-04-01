<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Price extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $casts = [
        'upgradeable_price_ids' => 'json',
        'is_test_product' => 'boolean',
    ];

    protected $appends = [
        "price_without_vat",
        "draw_price",
        "name"
    ];

    public function getPriceWithoutVatAttribute()
    {
        /** ekstra servis ücretleri dahil değilldir !!!!!!!! */
        $price = $this->price;
        $vatPercent = $this->product?->vat_percent ?? 0;

        return $price / (1 + ($vatPercent / 100));
    }

    public function getNameAttribute()
    {
        return $this->duration . " " . __(mb_strtolower($this->duration_unit)) . " (" . showBalance($this->price, true) . ")";
    }
    public function getDrawPriceAttribute()
    {
        $price = $this->price;
        if (!$price) return 0;
        return showBalance($price);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->with("category");
    }
}
