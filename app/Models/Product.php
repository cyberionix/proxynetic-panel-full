<?php

namespace App\Models;

use App\Library\Logger;
use App\Services\LocaltonetService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $casts = [
        "attrs"          => "json",
        'delivery_items' => 'json'
    ];
    protected $appends = ['price'];


    public static function testProducts()
    {
        return self::with('prices')->whereHas('prices', function ($query) {
            $query->where('is_test_product', 1);
        })->get()->map(function ($item) {
            $item->usable = false;
            if (Auth::check()) {
                $item->usable = true;

                if (Order::whereUserId(Auth::user()->id)->whereProductId($item->id)->exists()) {
                    $item->usable = false;
                }
            }
            $item->prices = $item->prices->filter(function ($price) {

                return $price->is_test_product == 1;
            });
            return $item;
        });
    }

    public function category():BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function prices():HasMany
    {
        return $this->hasMany(Price::class);
    }

    // TokenPool ilişkisi
    public function tokenPool()
    {
        $deliveryItems = $this->delivery_items;

        // Eğer delivery_items JSON içinde token_pool_id mevcutsa ilişkili TokenPool modelini çek
        if (isset($deliveryItems['token_pool_id'])) {
            return $this->hasOne(TokenPool::class, 'id',)->where('id', $deliveryItems['token_pool_id']);
        }

        return null;
    }

    // TokenPool ilişkisi
    public function getTokenPool()
    {
        $deliveryItems = $this->delivery_items;

        // Eğer delivery_items JSON içinde token_pool_id mevcutsa ilişkili TokenPool modelini çek
        if (isset($deliveryItems['token_pool_id'])) {
            return TokenPool::where('id', $deliveryItems['token_pool_id'])->first();
//            return $this->hasOne(TokenPool::class, 'id',)->where('id', $deliveryItems['token_pool_id']);
        }

        return null;
    }

    public function getNextAuthToken()
    {
        if ($this->delivery_type == "LOCALTONET" && isset($this->delivery_items["token_pool_id"])) {
            $tokenPool = TokenPool::find($this->delivery_items['token_pool_id']);
            if (!$tokenPool) return null;
            $tokens = $tokenPool->tokens;

            $service = new LocaltonetService();
            $authTokens = $service->getAuthTokens();
            if (@$authTokens["hasError"]) {
                Logger::error("PRODUCT_GET_ACTIVE_AUTH_TOKEN_ERROR", ["product_id" => $this->id, "errorCode" => @$authTokens["errorCode"], "errors" => @$authTokens["errors"]]);
            }
            $authTokens = collect(@$authTokens["result"])->whereIn("token", $tokens)->where("clientIsOnline", true)->first();

            return $authTokens ?: null;
        }
        return null;
    }

    protected function resolveV4Entries(): array
    {
        $di = $this->delivery_items ?? [];
        $poolId = $di['ip_pool_id'] ?? null;
        if ($poolId) {
            $pool = IpPool::find($poolId);
            return ($pool && is_array($pool->entries)) ? $pool->entries : [];
        }
        return $di['v4_entries'] ?? [];
    }

    public function getNextAuthTokenV4(): ?array
    {
        if ($this->delivery_type !== 'LOCALTONETV4') {
            return null;
        }
        $entries = $this->resolveV4Entries();
        if (! is_array($entries) || count($entries) === 0) {
            return null;
        }

        $validRows = [];
        foreach ($entries as $e) {
            if (! is_array($e)) {
                continue;
            }
            $token = trim((string) ($e['token'] ?? ''));
            if ($token === '') {
                continue;
            }
            $ips = $e['ips'] ?? [];
            $ips = is_array($ips)
                ? array_values(array_unique(array_filter(array_map('trim', $ips))))
                : [];

            $validRows[] = ['token' => $token, 'ips' => $ips];
        }

        if (count($validRows) === 0) {
            return null;
        }

        $row = $validRows[array_rand($validRows)];
        $token = $row['token'];
        $ips = $row['ips'];

        $selectedIp = '';
        if (count($ips) > 0) {
            $selectedIp = $ips[array_rand($ips)];
        }

        return [
            'token' => $token,
            'clientIsOnline' => true,
            'ips' => $ips,
            'selected_ip' => $selectedIp,
        ];
    }

    /**
     * LOCALTONETV4: Teslimattan önce tüm slotları tek seferde üretir — havuz IP'leri karıştırılır;
     * müşterinin bu üründe daha önce aldığı IP'ler ve aynı sipariş içinde tekrarlar hariç tutulur.
     *
     * @return list<array{token: string, clientIsOnline: true, ips: list<string>, selected_ip: string}>|null
     */
    public function allocateLocaltonetV4DeliveryPlan(Order $order, int $count): ?array
    {
        if ($this->delivery_type !== 'LOCALTONETV4' || $count < 1) {
            return null;
        }

        $entries = $this->resolveV4Entries();
        if (! is_array($entries) || count($entries) === 0) {
            return null;
        }

        $validRows = [];
        foreach ($entries as $e) {
            if (! is_array($e)) {
                continue;
            }
            $token = trim((string) ($e['token'] ?? ''));
            if ($token === '') {
                continue;
            }
            $ips = $e['ips'] ?? [];
            $ips = is_array($ips)
                ? array_values(array_unique(array_filter(array_map('trim', $ips))))
                : [];

            $validRows[] = ['token' => $token, 'ips' => $ips];
        }

        if (count($validRows) === 0) {
            return null;
        }

        $pairs = [];
        foreach ($validRows as $row) {
            foreach ($row['ips'] as $ip) {
                $ip = trim((string) $ip);
                if ($ip === '' || ! filter_var($ip, FILTER_VALIDATE_IP)) {
                    continue;
                }
                $pairs[] = [
                    'token' => $row['token'],
                    'ips' => $row['ips'],
                    'selected_ip' => $ip,
                ];
            }
        }

        if (count($pairs) === 0) {
            $plan = [];
            for ($i = 0; $i < $count; $i++) {
                $slot = $this->getNextAuthTokenV4();
                if (! $slot || ! isset($slot['token'])) {
                    Logger::error('LOCALTONET_V4_PLAN_FALLBACK_TOKEN_MISSING', ['product_id' => $this->id, 'order_id' => $order->id]);

                    return null;
                }
                $plan[] = $slot;
            }

            return $plan;
        }

        $userId = $order->user_id;
        $usedIps = ($userId !== null)
            ? Order::usedLocaltonetV4PoolIpsForUser((int) $userId, (int) $this->id, (int) $order->id)
            : [];
        $usedSet = array_fill_keys($usedIps, true);

        $pairs = array_values(array_filter($pairs, function ($p) use ($usedSet) {
            return empty($usedSet[$p['selected_ip']]);
        }));

        shuffle($pairs);

        $chosen = [];
        $batchIps = [];
        foreach ($pairs as $p) {
            if (count($chosen) >= $count) {
                break;
            }
            $ip = $p['selected_ip'];
            if ($ip === '' || isset($batchIps[$ip])) {
                continue;
            }
            $batchIps[$ip] = true;
            $chosen[] = $p;
        }

        if (count($chosen) < $count) {
            Logger::error('LOCALTONET_V4_INSUFFICIENT_UNIQUE_POOL_IPS', [
                'product_id' => $this->id,
                'order_id' => $order->id,
                'needed' => $count,
                'available_after_filter' => count($chosen),
                'pool_pairs' => count($pairs),
            ]);

            return null;
        }

        $out = [];
        foreach ($chosen as $p) {
            $out[] = [
                'token' => $p['token'],
                'clientIsOnline' => true,
                'ips' => $p['ips'],
                'selected_ip' => $p['selected_ip'],
            ];
        }

        return $out;
    }

    public function getNextAuthTokenForDelivery(): ?array
    {
        if ($this->delivery_type === 'LOCALTONET') {
            return $this->getNextAuthToken();
        }
        if ($this->delivery_type === 'LOCALTONETV4') {
            return $this->getNextAuthTokenV4();
        }

        return null;
    }

    public function periodOptions()
    {
        $options = [];
        foreach ($this->prices as $price) {
            $options[] = [
                "label" => $price->duration . " " . __(strtolower($price->duration_unit)),
                "value" => $price->id,
            ];
        }
        return $options;
    }

    public function findAttrsByServiceType($serviceType)
    {
        $productAttrs = null;
        if (is_array($this->attrs) && count($this->attrs) > 0) {
            $productAttrs = collect($this->attrs)->where("service_type", $serviceType)->first();
            if ($productAttrs) {
                foreach ($productAttrs["options"] as $key => $option) {
                    $productAttrs["options"][$key]["label"] = $option["label"];
                }
            }
        }

        return $productAttrs;
    }

    /* start::Attributes */
    public function getPriceAttribute($value)
    {
        if (!$value) return 0;
        return showBalance($value);
    }

    public function getVatPercentAttribute($value)
    {
        return $value ?? 0;
    }

    public function getPriceWithVatAttribute($value)
    {
        if (!$value) return 0;
        return showBalance($value);
    }
    /* end::Attributes */
}
