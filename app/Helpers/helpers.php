<?php
if (!function_exists('assetPortal')) {
    function assetPortal($path)
    {
        return url('assets_portal/' . $path);
    }
}

if (!function_exists('assetAdmin')) {
    function assetAdmin($path)
    {
        return url('assets_admin/' . $path);
    }
}

if (!function_exists('assetWeb')) {
    function assetWeb($path)
    {
        return url('assets_web/' . $path);
    }
}

if (!function_exists('brand')) {
    function brand($config = '')
    {
        return config('brand.' . $config);
    }
}

if (!function_exists('db_user_full_name_expr')) {
    /**
     * Ad + soyad birleştirme ifadesi (MySQL: CONCAT, SQLite: || — CONCAT SQLite'ta yok).
     */
    function db_user_full_name_expr(string $table = 'users'): string
    {
        $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();

        return $driver === 'sqlite'
            ? "({$table}.first_name || ' ' || {$table}.last_name)"
            : "CONCAT({$table}.first_name, ' ', {$table}.last_name)";
    }
}

if (!function_exists('defaultDateFormat')) {
    function defaultDateFormat($format = "d/m/Y")
    {
        return $format;
    }
}

if (!function_exists('defaultDateTimeFormat')) {
    function defaultDateTimeFormat($format = "d/m/Y H:i:s")
    {
        return $format;
    }
}

if (!function_exists('formatDateTimeInAppTimezone')) {
    /**
     * ISO8601 / UTC (ör. Localtonet API) gelen anı uygulama saat diliminde gösterir.
     */
    function formatDateTimeInAppTimezone($datetime, ?string $format = null): string
    {
        if ($datetime === null || $datetime === '') {
            return '';
        }
        $format = $format ?? defaultDateTimeFormat();

        return \Carbon\Carbon::parse($datetime)->timezone(config('app.timezone'))->format($format);
    }
}

if (!function_exists('convertDate')) {
    function convertDate($date, $format = null)
    {
        if (!$format) $format = defaultDateFormat();
        if (!$date) return "";

        $date = str_replace('/', '-', $date);
        $regEx = '/(\d{4})-(\d{2})-(\d{2})/';

        if (preg_match($regEx, $date)) {
            return date($format, strtotime($date));
        }

        return date("Y-m-d", strtotime($date));
    }
}

if (!function_exists('convertDateTime')) {
    function convertDateTime($date, $format = null)
    {
        if (!$date) return "";
        if (!$format) $format = defaultDateFormat() . " H:i:s";


        return convertDate($date, $format);
    }
}

if (!function_exists('phoneMask')) {
    function phoneMask($string)
    {
        return $string;
        if (!is_string($string)) return "";
        if (strlen($string) == 15) {
            return "9" . str_replace(["(", ")", " ", "-", "_", "?"], "", $string);
        } elseif (strlen($string) == 12 || strlen($string) == 13) {
            $string = str_replace(["+", "(", ")", " ", "-", "_", "?"], "", $string);
            return $string[1] . "(" . $string[2] . $string[3] . $string[4] . ") " . $string[5] . $string[6] . $string[7] . " " . $string[8] . $string[9] . $string[10] . $string[11];
        } else {
            return "";
        }
    }
}

if (!function_exists('showBalance')) {
    function showBalance($balance, $symbol = false)
    {
        $balance = number_format($balance, 2, ",", ".");
        if ($symbol) {
            return defaultCurrencySymbol() . $balance;
        }
        return $balance;
    }
}

if (!function_exists('commaToDot')) {
    function commaToDot($number)
    {
        return str_replace(",", ".", str_replace(".", "", $number));
    }
}

if (!function_exists('defaultCurrencySymbol')) {
    function defaultCurrencySymbol($symbol = "₺")
    {
        return $symbol;
    }
}

if (!function_exists('getVats')) {
    function getVats()
    {
        return [
            0 => 0,
            1 => 1,
            2 => 10,
            3 => 20,
        ];
    }
}

if (!function_exists('addDurationToDate')) {
    function addDurationToDate($duration, $unit, \Carbon\Carbon $date = null)
    {
        if (!$date) $date = today();
        return match ($unit) {
            'DAILY' => $date->addDays($duration),
            'WEEKLY' => $date->addWeeks($duration),
            'MONTHLY' => $date->addMonths($duration),
            'YEARLY' => $date->addYears($duration),
            'ONE_TIME' => null,
            default => false,
        };
    }
}

if (!function_exists('convertDurationText')) {
    function convertDurationText($duration)
    {
        return match ($duration) {
            'DAILY' => 'Gün',
            'WEEKLY' => 'Hafta',
            'MONTHLY' => 'Ay',
            'YEARLY' => 'Yıl',
            'ONE_TIME' => __("one_time"),
            default => false,
        };
    }
}

if (!function_exists('convertByteToGB')) {
    function convertByteToGB($bayt)
    {
        if (!$bayt) return 0;
        return number_format($bayt / 1073741824, 2, '.', '');
    }
}

if (!function_exists('getProxyTypeForAttrs')) {
    function getProxyTypeForAttrs()
    {
        return [
            "type" => "radio",
            "service_type" => "protocol_select",
            "name" => "protocol_secimi",
            "label" => "Protocol Seçimi<br>HTTP & Socks5",
            "options" => [
                [
                    "label" => "Http / Http(s)",
                    "value" => "http",
                    "price" => 0.00
                ],
                [
                    "label" => "Socks5",
                    "value" => "socks5",
                    "price" => 15.00
                ]
            ]
        ];
    }
}

if (!function_exists('getQuotaForAttrs')) {
    function getQuotaForAttrs()
    {
        return [
            "type" => "select",
            "service_type" => "quota",
            "name" => "quota",
            "attrs" => "data-hide-search=true",
            "options" => [
                [
                    "label" => "2 GB",
                    "value" => "2",
                    "price" => 2.00
                ]
            ],
        ];
    }
}

if (!function_exists('getPProxyQuotaForAttrs')) {
    function getPProxyQuotaForAttrs()
    {
        return [
            "type" => "select",
            "service_type" => "pproxy_quota",
            "name" => "pproxy_quota",
            "attrs" => "data-hide-search=true",
            "options" => [
                [
                    "label" => "1 GB",
                    "value" => "1",
                    "price" => 0.00
                ]
            ],
        ];
    }
}

if (!function_exists('getQuotaDurationForAttrs')) {
    function getQuotaDurationForAttrs()
    {
        return [
            "type" => "select",
            "service_type" => "quota_duration",
            "name" => "quota_duration",
            "attrs" => "data-hide-search=true",
            "options" => [
                [
                    "label" => "1 Gün - 1 GB - ₺100",
                    "value" => "1_gun_1_gb_tl100",
                    "gb" => "1",
                    "duration" => "1",
                    "duration_unit" => "DAILY",
                    "price" => 100.00
                ]
            ],
        ];
    }
}

if (!function_exists('getAdditionalServices')) {
    function getAdditionalServices($product, $name, $value)
    {
        if (!$product){
            return [
                "label" => "",
                "value" => "",
                "price" => 0.00,
                "renew" => false
            ];
        }

        $service = collect($product->attrs)->where("name", $name)->first();
        if (!$service){
            return [
                "label" => "",
                "value" => "",
                "price" => 0.00,
                "renew" => false
            ];
        }

        foreach ($service["options"] as $option) {
            if ($option["value"] == $value){
                return [
                    "name" => $service["name"],
                    "service_type" => $service["service_type"],
                    "renew" => $service["service_type"] == "protocol_select",
                    "label" => $option["label"],
                    "value" => $option["value"],
                    "price" => $option["price"],
                    "price_without_vat" => $option["price"] / (1 + ($product->vat_percent / 100))
                ];
            }
        }

        return [
            "label" => "",
            "value" => "",
            "price" => 0.00,
            "renew" => false
        ];
    }
}

if (!function_exists('localtonet_tunnel_result_is_socks')) {
    /**
     * GetTunnelDetail / önbellek sonucunda protokol SOCKS mu? API bazen 7, "ProxySocks", ProtocolType vb. döner.
     */
    function localtonet_tunnel_result_is_socks(?array $result): bool
    {
        if ($result === null || $result === []) {
            return false;
        }

        $socksCode = (int) config('services.localtonet_v4.v2_protocol_socks', 7);
        $httpCode = (int) config('services.localtonet_v4.v2_protocol_http', 6);
        $raw = $result['protocolType'] ?? $result['ProtocolType'] ?? null;

        if ($raw !== null && $raw !== '' && is_numeric($raw)) {
            $n = (int) $raw;
            if ($n === $socksCode) {
                return true;
            }
            if ($n === $httpCode) {
                return false;
            }
        }

        if (is_string($raw) && $raw !== '') {
            $lr = strtolower($raw);
            if (str_contains($lr, 'sock')) {
                return true;
            }
            if (str_contains($lr, 'proxyhttp') || str_contains($lr, 'http')) {
                return false;
            }
        }

        $draw = strtolower((string) ($result['drawProtocolType'] ?? ''));
        if (str_contains($draw, 'sock')) {
            return true;
        }
        if (str_contains($draw, 'http')) {
            return false;
        }

        return false;
    }
}

if (!function_exists('user_friendly_mail_exception_message')) {
    /**
     * Mailer/Mailjet vb. ham JSON/İngilizce hata metnini kısa Türkçe açıklamaya indirger (log ve destek için).
     */
    function user_friendly_mail_exception_message(\Throwable $e): string
    {
        $msg = $e->getMessage();
        if (preg_match('/401|API key authentication|authorization failure|unauthorized/i', $msg)) {
            return 'E-posta sağlayıcısı API anahtarı geçersiz veya süresi dolmuş (401). .env içindeki MAIL / Mailjet ayarlarını kontrol edin.';
        }
        if (preg_match('/403|Forbidden/i', $msg)) {
            return 'E-posta sağlayıcısı API erişimi reddedildi (403).';
        }
        if (preg_match('/429|Too Many Requests|rate limit/i', $msg)) {
            return 'E-posta gönderim limiti aşıldı; kısa süre sonra tekrar deneyin.';
        }

        return \Illuminate\Support\Str::limit($msg, 500);
    }
}

if (!function_exists('activity_log_label')) {
    /**
     * Kayıtlı rota anahtarını (portal_supports_index) okunabilir Türkçe cümleye çevirir.
     */
    function activity_log_label(string $routeKey): string
    {
        $fullKey = 'activity_logs.'.$routeKey;
        $trans = __($fullKey);
        if ($trans !== $fullKey) {
            return $trans;
        }

        return __('activity_logs.fallback_unknown');
    }
}

if (!function_exists('getTpExtraDurationForAttrs')) {
    function getTpExtraDurationForAttrs()
    {
        return [
            "type" => "select",
            "service_type" => "tp_extra_duration",
            "name" => "tp_extra_duration",
            "attrs" => "data-hide-search=true",
            "options" => [
                ["label" => "1 Ay Ek Süre", "value" => "1_ay", "duration" => "1", "duration_unit" => "MONTHLY", "price" => 50.00]
            ],
        ];
    }
}

if (!function_exists('getTpChangeIpsForAttrs')) {
    function getTpChangeIpsForAttrs()
    {
        return [
            "type" => "action",
            "service_type" => "tp_change_ips",
            "name" => "tp_change_ips",
            "label" => "IP'leri Değiştir",
            "price" => 0.00,
        ];
    }
}

if (!function_exists('getTpSubnetIpsForAttrs')) {
    function getTpSubnetIpsForAttrs()
    {
        return [
            "type" => "action",
            "service_type" => "tp_subnet_ips",
            "name" => "tp_subnet_ips",
            "label" => "Her Subnetten Farklı IP",
            "price" => 0.00,
        ];
    }
}

if (!function_exists('getTpClassIpsForAttrs')) {
    function getTpClassIpsForAttrs()
    {
        return [
            "type" => "action",
            "service_type" => "tp_class_ips",
            "name" => "tp_class_ips",
            "label" => "Her Class IP'den Farklı IP",
            "price" => 0.00,
        ];
    }
}

if (!function_exists('selectIpsBySubnet')) {
    /**
     * Her /24 subnetten en fazla 1 IP seçer.
     */
    function selectIpsBySubnet(array $ipItems, int $count): array
    {
        $usedSubnets = [];
        $selected = [];
        shuffle($ipItems);
        foreach ($ipItems as $item) {
            $ip = is_array($item) ? $item['ip'] : $item;
            $parts = explode('.', $ip);
            if (count($parts) < 4) continue;
            $subnet = $parts[0] . '.' . $parts[1] . '.' . $parts[2];
            if (isset($usedSubnets[$subnet])) continue;
            $usedSubnets[$subnet] = true;
            $selected[] = $item;
            if (count($selected) >= $count) break;
        }
        return $selected;
    }
}

if (!function_exists('selectIpsByClass')) {
    /**
     * Her /16 (Class B) bloğundan en fazla 1 IP seçer.
     */
    function selectIpsByClass(array $ipItems, int $count): array
    {
        $usedClasses = [];
        $selected = [];
        shuffle($ipItems);
        foreach ($ipItems as $item) {
            $ip = is_array($item) ? $item['ip'] : $item;
            $parts = explode('.', $ip);
            if (count($parts) < 4) continue;
            $classBlock = $parts[0] . '.' . $parts[1];
            if (isset($usedClasses[$classBlock])) continue;
            $usedClasses[$classBlock] = true;
            $selected[] = $item;
            if (count($selected) >= $count) break;
        }
        return $selected;
    }
}

function convertToTurkishUppercase($string) {
    $string = str_replace(['ı', 'i'], ['I', 'İ'], $string);

    $string = mb_strtoupper($string, 'UTF-8');

    $string = str_replace('İ', 'İ', $string);

    return $string;
}
