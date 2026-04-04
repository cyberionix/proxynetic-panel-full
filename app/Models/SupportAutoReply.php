<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupportAutoReply extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'delay_minutes' => 'integer',
        'sort_order' => 'integer',
        'trigger_product_category_ids' => 'array',
        'skip_if_admin_replied' => 'boolean',
        'is_priority' => 'boolean',
    ];

    public function getProductCategoriesAttribute()
    {
        if (empty($this->trigger_product_category_ids)) {
            return collect();
        }
        return ProductCategory::whereIn('id', $this->trigger_product_category_ids)->get();
    }

    public const TRIGGER_EVENTS = [
        'TICKET_CREATED' => 'Yeni Talep Oluşturulduğunda',
        'CUSTOMER_REPLIED' => 'Müşteri Yanıt Verdiğinde',
        'TICKET_RESOLVED' => 'Talep Çözüldü Olarak İşaretlendiğinde',
    ];

    public const DEPARTMENTS = [
        '' => 'Tüm Departmanlar',
        'GENERAL' => 'Genel',
        'ORDER' => 'Sipariş',
        'ACCOUNTING' => 'Muhasebe',
        'TECHNICAL_SUPPORT' => 'Teknik Destek',
    ];

    public function replaceVariables(Support $support): string
    {
        $content = $this->message;
        $content = str_replace('{user_name}', $support->user->full_name ?? '', $content);
        $content = str_replace('{ticket_id}', (string) $support->id, $content);
        $content = str_replace('{ticket_subject}', $support->subject ?? '', $content);
        $content = str_replace('{department}', __(strtolower($support->department ?? '')), $content);
        return $content;
    }
}
