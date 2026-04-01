<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThreeProxyLog extends Model
{
    protected $guarded = [];

    protected $casts = [
        'proxy_data' => 'json',
        'ip_list' => 'json',
        'metadata' => 'json',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    const ACTION_CREATED = 'CREATED';
    const ACTION_REINSTALLED = 'REINSTALLED';
    const ACTION_STOPPED = 'STOPPED';
    const ACTION_STARTED = 'STARTED';
    const ACTION_EXPIRED = 'EXPIRED';
    const ACTION_RENEWED = 'RENEWED';
    const ACTION_CREDENTIALS_CHANGED = 'CREDENTIALS_CHANGED';
    const ACTION_PORT_CHANGED = 'PORT_CHANGED';
    const ACTION_DELETED = 'DELETED';
    const ACTION_EXPIRE_EXTENDED = 'EXPIRE_EXTENDED';

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pool()
    {
        return $this->belongsTo(ThreeProxyPool::class, 'pool_id');
    }

    public static function log(
        int     $orderId,
        string  $action,
        array   $proxyData = [],
        array   $metadata = [],
        ?int    $userId = null,
        ?int    $poolId = null,
        ?string $username = null,
        ?string $password = null,
    ): self
    {
        $ipList = array_values(array_unique(array_filter(
            array_column($proxyData, 'ip')
        )));

        $lastCreated = self::where('order_id', $orderId)
            ->where('action', self::ACTION_CREATED)
            ->orWhere(function ($q) use ($orderId) {
                $q->where('order_id', $orderId)
                    ->where('action', self::ACTION_REINSTALLED);
            })
            ->orWhere(function ($q) use ($orderId) {
                $q->where('order_id', $orderId)
                    ->where('action', self::ACTION_RENEWED);
            })
            ->orWhere(function ($q) use ($orderId) {
                $q->where('order_id', $orderId)
                    ->where('action', self::ACTION_STARTED);
            })
            ->latest()
            ->first();

        $startedAt = $lastCreated?->created_at;
        $endedAt = null;
        $durationSeconds = null;

        if (in_array($action, [self::ACTION_STOPPED, self::ACTION_EXPIRED, self::ACTION_DELETED, self::ACTION_REINSTALLED])) {
            $endedAt = now();
            if ($startedAt) {
                $durationSeconds = $endedAt->diffInSeconds($startedAt);
            }
        }

        return self::create([
            'order_id' => $orderId,
            'user_id' => $userId,
            'pool_id' => $poolId,
            'action' => $action,
            'proxy_data' => count($proxyData) > 0 ? $proxyData : null,
            'ip_list' => count($ipList) > 0 ? $ipList : null,
            'proxy_count' => count($proxyData),
            'username' => $username,
            'password' => $password,
            'metadata' => count($metadata) > 0 ? $metadata : null,
            'started_at' => in_array($action, [self::ACTION_CREATED, self::ACTION_REINSTALLED, self::ACTION_RENEWED, self::ACTION_STARTED]) ? now() : $startedAt,
            'ended_at' => $endedAt,
            'duration_seconds' => $durationSeconds,
        ]);
    }

    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            self::ACTION_CREATED => 'Oluşturuldu',
            self::ACTION_REINSTALLED => 'Yeniden Kuruldu',
            self::ACTION_STOPPED => 'Durduruldu',
            self::ACTION_STARTED => 'Başlatıldı',
            self::ACTION_EXPIRED => 'Süresi Doldu',
            self::ACTION_RENEWED => 'Yenilendi',
            self::ACTION_CREDENTIALS_CHANGED => 'Kimlik Değiştirildi',
            self::ACTION_PORT_CHANGED => 'Port Değiştirildi',
            self::ACTION_DELETED => 'Silindi',
            self::ACTION_EXPIRE_EXTENDED => 'Süre Uzatıldı',
            default => $this->action,
        };
    }

    public function getActionBadgeAttribute(): string
    {
        $color = match ($this->action) {
            self::ACTION_CREATED, self::ACTION_STARTED, self::ACTION_RENEWED => 'success',
            self::ACTION_STOPPED, self::ACTION_EXPIRED => 'warning',
            self::ACTION_DELETED => 'danger',
            self::ACTION_REINSTALLED => 'info',
            self::ACTION_CREDENTIALS_CHANGED, self::ACTION_PORT_CHANGED, self::ACTION_EXPIRE_EXTENDED => 'primary',
            default => 'secondary',
        };

        return '<span class="badge badge-' . $color . '">' . $this->action_label . '</span>';
    }

    public function getDurationHumanAttribute(): ?string
    {
        if ($this->duration_seconds === null) {
            return null;
        }

        $s = $this->duration_seconds;
        if ($s < 60) return $s . ' sn';
        if ($s < 3600) return floor($s / 60) . ' dk';
        if ($s < 86400) return floor($s / 3600) . ' sa ' . floor(($s % 3600) / 60) . ' dk';
        return floor($s / 86400) . ' gün ' . floor(($s % 86400) / 3600) . ' sa';
    }
}
