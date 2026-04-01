<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ThreeProxyService
{
    protected $ip,$port,$base_url;
    public function __construct($ip,$port,$api_key)
    {
        $this->ip = $ip;
        $this->port = $port;
        $this->base_url = 'http://'.$ip.':'.$port.'/';
        $this->api_key = $api_key;
    }

    public function getProxies()
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->api_key,
        ])->get($this->base_url . "list-proxies");

        return $response->json();
    }

    public function createProxyHelper($adet, $apikey, $url, $port, $authUser, $authPassword, $protocol, $allow, $deny, $limit = null) {
        $allow = explode(",",$allow);
        $deny = explode(",",$deny);
        $data = [
            'apikey' => $apikey,
            'adet' => $adet,
            'port' => $port,
            'authUser' => $authUser,
            'authPassword' => $authPassword,
            'protocol' => $protocol,
            'allow' => $allow,
            'deny' => $deny
        ];

        // Optional: Add limit if provided
        if ($limit !== null) {
            $data['limit'] = $limit;
        }

        $payload = json_encode($data);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload)
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    public function createProxy($ip_address, $port, $authUser, $authPassword, $protocol, $allow = [], $deny = [], $limit = '')
    {
        if (!is_array($allow)) $allow = explode(',',$allow);
        if (!is_array($deny)) $deny = explode(',',$deny);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->api_key,
        ])->post($this->base_url . "create-proxy",[
            'adet' => 1,
            'apikey' => $this->api_key,
            'external' => $ip_address,
            'port' => $port,
            'authUser' => $authUser,
            'authPassword' => $authPassword,
            'protocol' => $protocol,
            'limit' => $limit,
            'allow' => $allow,
            'deny' => $deny
        ]);

        return $response->json();
    }

}
