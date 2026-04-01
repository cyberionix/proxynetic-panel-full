<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BasketItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $casts = [
        'additional_services' => 'json',
        'is_test_product' => 'boolean',
    ];


    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function price(): BelongsTo
    {
        return $this->belongsTo(Price::class);
    }

    public function getAdditionalServices($name, $value)
    {
        return getAdditionalServices($this->product, $name, $value);
    }

    public function getPeriodOptions()
    {
        $prices = Price::whereProductId($this->product_id)->get();
        $options = [];
        foreach ($prices as $item){
            $options[] = [
                'value' => $item->id,
                'label' => $item->duration . " " . __(mb_strtolower($item->duration_unit)) . " (" . showBalance($item->price, true) . ")",
            ];
        }
        return $options;
    }
}
