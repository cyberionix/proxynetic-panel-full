<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Basket extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function items(): HasMany
    {
        return $this->hasMany(BasketItem::class)->with(["product.category", "price"]);
    }

    public function couponCode()
    {
        return $this->hasOne(CouponCode::class,'id','coupon_code_id');
    }
    public function basketSummary()
    {
        $__subTotal = 0;
        $__tax = 0;
        $__total = 0;

        foreach ($this->items as $item) {
            $servicePrice = 0; //kvd hariç service price
            if ($item->additional_services){
                foreach ($item->additional_services as $name => $value) {
                    $servicePrice += $item->getAdditionalServices($name, $value)["price_without_vat"];
                }
            }

            $subTotal = $item?->price?->price_without_vat + $servicePrice;
            $vat = ($subTotal * $item->product->vat_percent) / 100;
            $price = $subTotal + $vat; //ekstra hizmetle birlikte kdv dahil fiyat

            $__subTotal += $subTotal;
            $__tax += $vat;
            $__total += $price;
        }

        $summary = [
            "sub_total" => $__subTotal,
            "tax" => $__tax,
            "total" => $__total,
            "count" => $this->items ? count($this->items) : 0,
            'coupon_code' => null,
            'coupon_code_text' => null,
            'discount_amount' => 0,
            'real_total' => $__total
        ];


        if($this->coupon_code_id && $this->couponCode){
            $coupon = $this->couponCode;

            $summary['coupon_code_text'] = $this->couponCode->coupon_code;
            $summary['coupon_code'] = $this->couponCode->id;

            if ($coupon->type == 'PERCENT'){

                $summary['discount_amount'] = $summary['real_total']*$coupon->amount/100;
                $summary['real_total'] = $summary['real_total']-$summary['discount_amount'];

            }else if($coupon->type == 'FIXED'){

                $summary['discount_amount'] = $coupon->amount;
                $summary['real_total'] = $summary['real_total']-$summary['discount_amount'];
            }
            if ($summary['real_total'] <= 0){
                unset($summary['coupon_code']);
                unset($summary['coupon_code_text']);
                unset($summary['discount_amount']);
                $summary['real_total'] = $summary['total'];
            }
        }

        return $summary;

    }

    public function itemsCount()
    {
        return count($this->items);
    }
}
