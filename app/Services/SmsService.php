<?php

namespace App\Services;

use App\Library\Logger;
use App\Services\Sms\IletiMerkezi\Exceptions\CouldNotSendNotification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class SmsService
{

    private $key, $hash, $origin, $config, $baseUrl;

    public function __construct()
    {
        $this->config = config('services.sms.iletimerkezi');
        $this->key = Arr::get($this->config, 'key');
        $this->hash = Arr::get($this->config, 'secret');
        $this->origin = Arr::get($this->config, 'origin');
        $this->baseUrl = 'https://api.iletimerkezi.com/v1';
    }

    public function getBalance()
    {
        $data = $this->postData([], 'get-balance/json');

        if ($data['response']['status']['code'] === 200){
            return $data['response']['balance']['sms'] ?? 0;
        }

        return 0;
    }

    /**
     * @param $post_data
     * @param $endPoint
     * @return array|false|mixed
     */
    protected function postData($post_data = [], $endPoint = '')
    {
        $data = [
            'request' => [
                'authentication' => [
                    'key'  => $this->key,
                    'hash' => $this->hash,
                ]
            ]
        ];

        $data = array_merge($data, $post_data);

        try {
            return Http::connectTimeout(10)
                ->timeout(25)
                ->post($this->baseUrl . '/' . $endPoint, $data)
                ->throw()
                ->json();
        } catch (\Exception $exception) {
            Logger::error('ILETIMERKEZI_SMS_SERVICE_ERROR',['data' => $data]);
        }

        return false;
    }
}
