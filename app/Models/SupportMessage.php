<?php

namespace App\Models;

use App\Services\AdminNotificationService;
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
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->user_ip = Request::ip();
        });

        static::created(function ($model) {
            try {
                if ($model->admin_id) {
                    $model->support->user->notify(new \App\Notifications\SupportAnsweredNotification($model->support));
                } else {
                    $isFirstMessage = SupportMessage::whereSupportId($model->support_id)->count() == 1;
                    if (!$isFirstMessage) {
                        AdminNotificationService::supportMessageCreated($model->support);
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
