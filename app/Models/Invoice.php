<?php

namespace App\Models;

use App\Library\EInvoiceManager;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Queue;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    public static $skipCreatedNotification = false;

    protected $guarded = [];
    protected $casts = [
        "invoice_address" => "json",
        "e_document_info" => "json",
        "formalized_at" => "datetime:Y-m-d H:i:s",
        "invoice_date" => "date",
        "due_date" => "date",
        "no_auto_merge" => "boolean",
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($invoice) {
            if (empty($invoice->share_token)) {
                $invoice->share_token = base64_encode(random_bytes(32));
            }
        });
        static::created(function ($invoice) {
            if (static::$skipCreatedNotification) return;
            if ($invoice->user && $invoice->status === 'PENDING') {
                \App\Services\NotificationTemplateService::send('invoice_created', $invoice->user, [
                    'fatura_no' => $invoice->invoice_number ?? $invoice->id,
                    'tutar' => number_format($invoice->total_price_with_vat ?? 0, 2, ',', '.'),
                    'son_odeme_tarihi' => $invoice->due_date?->format('d/m/Y') ?? '',
                    'fatura_url' => url('/invoices/' . $invoice->id),
                ]);
            }
        });
        static::deleted(function ($invoice) {
            $EInvoiceManager = app(EInvoiceManager::class);
            $EInvoiceManager->deleteInvoice($invoice);
        });
    }

    public static function generateInvoiceNumber()
    {
        $lastInvoice = Invoice::orderBy('id', 'desc')->first();
        $invoiceNumber = $lastInvoice ? ++$lastInvoice->invoice_number : date("Y") . "0001";

        while (Invoice::whereInvoiceNumber($invoiceNumber)->exists()) {
            $invoiceNumber++;
        }

        return $invoiceNumber;
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, "id", "user_id");
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, "invoice_id", "id")->with("order");
    }

    public function couponCode()
    {
        return $this->hasOne(CouponCode::class,'id','coupon_code_id');
    }

    public function getTotalPriceWithVatAttribute()
    {
        $realTotal = $this->attributes['real_total'] ?? null;
        $totalWithVat = $this->attributes['total_price_with_vat'] ?? 0;
        return ($realTotal && $realTotal > 0) ? $realTotal : $totalWithVat;
    }

    public function drawStatus($customClass = null)
    {
        $text = __(mb_strtolower($this->status));
        switch ($this->status) {
            case "PENDING":
                return '<span class="badge badge-danger ' . $customClass . '">' . __("waiting_for_payment") . '</span>';
            case "PAID":
                return '<span class="badge badge-success ' . $customClass . '">' . $text . '</span>';
            case "CANCELLED":
                return '<span class="badge badge-secondary ' . $customClass . '">' . $text . '</span>';
        }
    }

    public function hasPendingCheckout()
    {
        return $this->hasOne(Checkout::class)->where('status', 'WAITING_APPROVAL')->exists();;
    }

    public function isPaid()
    {
        return $this->status == "PAID";
    }

    public function checkout(): HasOne
    {
        return $this->hasOne(Checkout::class)->latest();
    }

    public function paymentAreaShowWallet()
    {
        $isWallet = true;
        if (in_array("BALANCE", $this->items->pluck("type")->toArray())){
            $isWallet = false;
        }
        return $isWallet;
    }
}
