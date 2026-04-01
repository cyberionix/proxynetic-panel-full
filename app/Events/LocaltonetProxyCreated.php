<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LocaltonetProxyCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Order $order;

    public $authToken;

    /** @var list<string> */
    public array $v4RestrictionIps;

    /** Teslimat sırasında sipariş henüz kaydedilmeden önce dinleyicilerin doğru tünelde işlem yapması için (çoklu V4). */
    public ?int $tunnelId = null;

    /** Çoklu V4 teslimatta başlık ayırıcı (1 tabanlı). */
    public ?int $tunnelOrdinal = null;

    public ?int $tunnelCount = null;

    /** Toplu v2 kurulumda bulk gövdesinde kullanılan sabit kullanıcı/şifre (dinleyicide aynı değerlerle SetAuthentication çağrılır). */
    public ?string $presetAuthUsername = null;

    public ?string $presetAuthPassword = null;

    /**
     * Create a new event instance.
     *
     * @param  list<string>  $v4RestrictionIps  LOCALTONETV4 ürünlerinde token satırına bağlı IP listesi (boş olabilir)
     */
    public function __construct(Order $order, $authToken, array $v4RestrictionIps = [], ?int $tunnelId = null, ?int $tunnelOrdinal = null, ?int $tunnelCount = null, ?string $presetAuthUsername = null, ?string $presetAuthPassword = null)
    {
        $this->order = $order;
        $this->authToken = $authToken;
        $this->v4RestrictionIps = $v4RestrictionIps;
        $this->tunnelId = $tunnelId;
        $this->tunnelOrdinal = $tunnelOrdinal;
        $this->tunnelCount = $tunnelCount;
        $this->presetAuthUsername = $presetAuthUsername;
        $this->presetAuthPassword = $presetAuthPassword;
    }
}
