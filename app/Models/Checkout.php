<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Checkout extends Model
{
    use HasFactory, SoftDeletes;

    const BANK_TRANSFER = 'TRANSFER';
    const CREDIT_CARD = 'CREDIT_CARD';

    protected $guarded = [];

    protected $casts = [
        "paid_at" => 'datetime:Y-m-d H:i:s',
        "extra_params" => 'json',
    ];

    protected $appends = ["draw_amount"];

    protected static function boot()
    {
        parent::boot();

    }

    public function getPaidAtAttribute($value)
    {
        if (!$value) return null;
        return Carbon::createFromFormat('Y-m-d H:i:s', $value)->format(defaultDateTimeFormat());
    }

    public function getDrawAmountAttribute()
    {
        if (!$this->amount) return null;
        return showBalance($this->amount, true);
    }

    public static function getNextOrderNumber()
    {
        $lastOrder = Checkout::whereNotNull('remote_order_number')->orderBy('id', 'desc')->first();
        return ($lastOrder && $lastOrder->remote_order_number) ? intval(intval($lastOrder->remote_order_number)+ 19).rand(100,999) : date('Y') . '0099';
    }

    public static function getNextReferenceCode()
    {
        $lastCheckout = Checkout::select('reference_code')->orderBy('reference_code', 'desc')->first();
        return ($lastCheckout) ? $lastCheckout->reference_code + 13 : date('Y') . '0001';
    }

    public function user():HasOne
    {
        return $this->hasOne(User::class, "id", "user_id");
    }

    public function basket():BelongsTo
    {
        return $this->belongsTo(Basket::class);
    }

    public function invoice():BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
