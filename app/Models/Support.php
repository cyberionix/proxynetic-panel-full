<?php

namespace App\Models;

use App\Services\AdminNotificationService;
use App\Traits\SupportAttributes;
use App\Traits\SupportEventHandlers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use function Symfony\Component\Translation\t;

class Support extends Model
{
    use HasFactory, SoftDeletes, SupportAttributes, SupportEventHandlers;

    protected $guarded = [];
    protected static function booted()
    {
        parent::booted();
        // Kontrol her sorguda yapılmalı; model erken yüklendiğinde admin oturumu henüz yoksa yanlışlıkla scope eklenmez.
        static::addGlobalScope('for_user', function ($query) {
            if (Auth::guard('admin')->check()) {
                return;
            }
            $query->where($query->getModel()->getTable().'.user_id', Auth::id());
        });

        static::created(function ($model) {
            AdminNotificationService::supportCreated($model);
        });
    }
    public function user(): BelongsTo
    {
        return $this->BelongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportMessage::class)->with("admin")->orderBy("created_at", "DESC");
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function drawStatusBadge()
    {
        $text = __(strtolower($this->status));
        switch ($this->status){
            case "WAITING_FOR_AN_ANSWER":
                return '<span class="badge badge-danger badge-sm">' . $text . '</span>';
            case "ANSWERED":
                return '<span class="badge badge-secondary badge-sm">' . $text . '</span>';
            case "RESOLVED":
                return '<span class="badge badge-success badge-sm">' . $text . '</span>';
            default:
                return null;
        }
    }
}
