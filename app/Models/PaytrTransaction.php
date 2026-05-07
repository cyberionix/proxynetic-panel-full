<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaytrTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'paytr_transactions';

    protected $fillable = [
        'reference_uuid',
        'checkout_id',
        'invoice_id',
        'user_id',
        'merchant_oid',
        'type',
        'status',
        'amount',
        'currency',
        'test_mode',
        'paytr_status',
        'paytr_total_amount',
        'paytr_payment_type',
        'paytr_installment',
        'paytr_failed_reason_code',
        'paytr_failed_reason_msg',
        'request_payload',
        'response_payload',
        'callback_payload',
        'callback_received_at',
    ];

    protected $casts = [
        'test_mode'            => 'boolean',
        'amount'               => 'decimal:2',
        'paytr_total_amount'   => 'decimal:2',
        'paytr_installment'    => 'integer',
        'request_payload'      => 'array',
        'response_payload'     => 'array',
        'callback_payload'     => 'array',
        'callback_received_at' => 'datetime',
    ];

    public function checkout()
    {
        return $this->belongsTo(Checkout::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function scopeTestMode($query)
    {
        return $query->where('test_mode', true);
    }

    public function scopeLive($query)
    {
        return $query->where('test_mode', false);
    }

    public function scopeSuccessful($query)
    {
        return $query->whereIn('status', ['success', 'token_issued']);
    }
}
