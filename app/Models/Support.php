<?php

namespace App\Models;

use App\Services\AdminNotificationService;
use App\Services\NotificationTemplateService;
use App\Services\SupportAutoReplyService;
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
            try {
                AdminNotificationService::supportCreated($model);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('SUPPORT_ADMIN_NOTIFY_FAIL', ['id' => $model->id, 'error' => $e->getMessage()]);
            }
            try {
                SupportAutoReplyService::handleEvent('TICKET_CREATED', $model);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('SUPPORT_AUTO_REPLY_FAIL', ['id' => $model->id, 'error' => $e->getMessage()]);
            }
            try {
                if ($model->user) {
                    NotificationTemplateService::send('support_created', $model->user, [
                        'talep_no' => $model->id,
                        'konu' => $model->subject ?? '',
                        'talep_url' => url('/supports/show/' . $model->id),
                    ]);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('SUPPORT_USER_NOTIFY_FAIL', ['id' => $model->id, 'error' => $e->getMessage()]);
            }
            try {
                (new \App\Services\TelegramService())->sendSupportNotification($model);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('SUPPORT_TELEGRAM_NOTIFY_FAIL', ['id' => $model->id, 'error' => $e->getMessage()]);
            }
        });

        static::updated(function ($model) {
            if ($model->isDirty('status') && $model->user) {
                try {
                    $vars = [
                        'talep_no' => $model->id,
                        'konu' => $model->subject ?? '',
                        'talep_url' => url('/supports/show/' . $model->id),
                    ];
                    if ($model->status === 'RESOLVED') {
                        SupportAutoReplyService::handleEvent('TICKET_RESOLVED', $model);
                        NotificationTemplateService::send('support_resolved', $model->user, $vars);
                    } elseif ($model->status === 'IN_PROGRESS' || $model->status === 'WAITING_FOR_AN_ANSWER') {
                        NotificationTemplateService::send('support_in_progress', $model->user, $vars);
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('SUPPORT_STATUS_NOTIFY_FAIL', ['id' => $model->id, 'error' => $e->getMessage()]);
                }
            }
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
