<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'channel', 'target_type', 'target_filters',
        'sms_content', 'mail_subject', 'mail_content',
        'status', 'total_recipients', 'sent_sms', 'sent_mail',
        'failed_count', 'sent_at', 'created_by',
    ];

    protected $casts = [
        'target_filters' => 'array',
        'sent_at' => 'datetime',
    ];

    public function resolveRecipients(): \Illuminate\Database\Eloquent\Collection
    {
        $query = User::query();
        $filters = $this->target_filters ?? [];

        switch ($this->target_type) {
            case 'user_group':
                if (!empty($filters['user_group_ids'])) {
                    $query->whereIn('user_group_id', $filters['user_group_ids']);
                }
                break;

            case 'product_category':
                if (!empty($filters['category_ids'])) {
                    $catIds = $filters['category_ids'];
                    $query->whereHas('orders', function ($q) use ($catIds) {
                        $q->whereHas('product', function ($pq) use ($catIds) {
                            $pq->whereIn('category_id', $catIds);
                        });
                    });
                }
                break;

            case 'product':
                if (!empty($filters['product_ids'])) {
                    $query->whereHas('orders', function ($q) use ($filters) {
                        $q->whereIn('product_id', $filters['product_ids']);
                    });
                }
                break;

            case 'active_orders':
                $query->whereHas('orders', function ($q) {
                    $q->where('status', 'ACTIVE');
                });
                break;

            case 'custom':
                if (!empty($filters['user_ids'])) {
                    $query->whereIn('id', $filters['user_ids']);
                }
                break;

            case 'all':
            default:
                break;
        }

        if ($this->channel === 'sms') {
            $query->whereNotNull('phone')->where('phone', '!=', '');
        } elseif ($this->channel === 'mail') {
            $query->whereNotNull('email')->where('email', '!=', '');
        } else {
            $query->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->whereNotNull('phone')->where('phone', '!=', '');
                })->orWhere(function ($sub) {
                    $sub->whereNotNull('email')->where('email', '!=', '');
                });
            });
        }

        return $query->get();
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\Admin::class, 'created_by');
    }

    public static function getTargetTypeLabel(string $type): string
    {
        return match ($type) {
            'all' => 'Tüm Müşteriler',
            'user_group' => 'Müşteri Grubu',
            'product_category' => 'Ürün Kategorisi',
            'product' => 'Belirli Ürün',
            'active_orders' => 'Aktif Siparişi Olanlar',
            'custom' => 'Manuel Seçim',
            default => ucfirst($type),
        };
    }
}
