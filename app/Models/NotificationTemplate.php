<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotificationTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'key',
        'category',
        'title',
        'sms_enabled',
        'mail_enabled',
        'admin_sms_enabled',
        'admin_mail_enabled',
        'sms_content',
        'mail_subject',
        'mail_content',
        'variables',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'sms_enabled' => 'boolean',
        'mail_enabled' => 'boolean',
        'admin_sms_enabled' => 'boolean',
        'admin_mail_enabled' => 'boolean',
        'is_active' => 'boolean',
        'variables' => 'array',
    ];

    public static function findByKey(string $key): ?self
    {
        return static::where('key', $key)->first();
    }

    public function renderSms(array $data = []): string
    {
        return self::replaceVariables($this->sms_content ?? '', $data);
    }

    public function renderMailSubject(array $data = []): string
    {
        return self::replaceVariables($this->mail_subject ?? '', $data);
    }

    public function renderMailContent(array $data = []): string
    {
        return self::replaceVariables($this->mail_content ?? '', $data);
    }

    private static function replaceVariables(string $template, array $data): string
    {
        foreach ($data as $key => $value) {
            $template = str_replace('{{' . $key . '}}', (string) $value, $template);
        }
        return $template;
    }

    public static function getCategoryLabel(string $category): string
    {
        return match ($category) {
            'genel' => 'Genel',
            'fatura' => 'Fatura',
            'siparis' => 'Sipariş',
            'destek' => 'Destek',
            default => ucfirst($category),
        };
    }

    public static function getCategoryIcon(string $category): string
    {
        return match ($category) {
            'genel' => 'fa-home',
            'fatura' => 'fa-file-invoice-dollar',
            'siparis' => 'fa-shopping-cart',
            'destek' => 'fa-headset',
            default => 'fa-bell',
        };
    }

    public static function getCategoryColor(string $category): string
    {
        return match ($category) {
            'genel' => 'primary',
            'fatura' => 'success',
            'siparis' => 'info',
            'destek' => 'warning',
            default => 'dark',
        };
    }
}
