<?php

namespace App\Models;

use App\Services\AdminNotificationService;
use App\Services\SupportAutoReplyService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class SupportMessage extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $casts = [
        'is_auto_reply' => 'boolean',
        'seen_at' => 'datetime',
    ];
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->user_ip = Request::ip();
        });

        static::created(function ($model) {
            try {
                if ($model->is_auto_reply) {
                    return;
                }

                if ($model->admin_id) {
                    $model->support->user->notify(new \App\Notifications\SupportAnsweredNotification($model->support));
                    \App\Services\NotificationTemplateService::send('support_replied', $model->support->user, [
                        'talep_no' => $model->support->id,
                        'konu' => $model->support->subject ?? '',
                        'talep_url' => url('/supports/show/' . $model->support_id),
                    ]);
                } else {
                    $isFirstMessage = SupportMessage::whereSupportId($model->support_id)
                        ->where('is_auto_reply', false)
                        ->count() == 1;
                    if ($isFirstMessage) {
                        try {
                            (new \App\Services\TelegramService())->sendSupportNotification($model->support, $model->message);
                        } catch (\Throwable $e) {
                            Log::error('SUPPORT_TELEGRAM_NOTIFY_FAIL', ['id' => $model->support_id, 'error' => $e->getMessage()]);
                        }
                    } else {
                        AdminNotificationService::supportMessageCreated($model->support);
                        SupportAutoReplyService::handleEvent('CUSTOMER_REPLIED', $model->support);
                    }
                }
            } catch (\Throwable $e) {
                Log::error('Destek mesajı kaydedildi ancak bildirim/e-posta gönderilemedi', [
                    'support_message_id' => $model->id,
                    'support_id' => $model->support_id,
                    'ozet' => user_friendly_mail_exception_message($e),
                    'exception' => $e->getMessage(),
                ]);
            }
        });
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function support(): BelongsTo
    {
        return $this->belongsTo(Support::class);
    }
}
