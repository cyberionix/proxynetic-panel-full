<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProxyLog extends Model
{
    protected $guarded = [];

    protected $casts = [
        'tunnel_ids'    => 'json',
        'proxy_details' => 'json',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Teslim edilen / değiştirilen / silinen proxy bilgilerini toplu kaydeder.
     */
    public static function logBulk(Order $order, array $tunnelIds, string $action, ?string $note = null, ?array $proxyDetails = null): void
    {
        if (count($tunnelIds) === 0) {
            return;
        }

        $details = $proxyDetails;
        if ($details === null) {
            $pi = $order->product_info ?? [];
            $creds = $pi['localtonet_v4_auth_credentials'] ?? [];
            $snaps = $pi['localtonet_v4_snapshots'] ?? [];
            $tunnelDetails = $pi['localtonet_v4_tunnel_details'] ?? [];
            $allIds = $pi['localtonet_v4_proxy_ids'] ?? [];

            $items = [];
            foreach ($tunnelIds as $tid) {
                $idx = array_search($tid, $allIds);
                $snap = $idx !== false ? ($snaps[$idx] ?? []) : [];
                $cred = $creds[(int) $tid] ?? [];
                $detail = $tunnelDetails[(int) $tid] ?? [];

                $items[] = array_filter([
                    'tunnel_id'   => $tid,
                    'ip'          => $snap['selected_ip'] ?? ($detail['ipAddress'] ?? null),
                    'port'        => $detail['serverPort'] ?? null,
                    'username'    => $cred['userName'] ?? null,
                    'password'    => $cred['password'] ?? null,
                ]);
            }
            $details = $items;
        }

        static::create([
            'user_id'       => $order->user_id,
            'order_id'      => $order->id,
            'action'        => $action,
            'tunnel_ids'    => $tunnelIds,
            'proxy_details' => $details,
            'note'          => $note,
        ]);
    }
}
