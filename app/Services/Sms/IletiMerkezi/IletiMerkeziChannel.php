<?php

namespace App\Services\Sms\IletiMerkezi;

use App\Models\SmsLog;
use App\Services\Sms\IletiMerkezi\Exceptions\CouldNotSendNotification;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class IletiMerkeziChannel
{
    /**
     * Login to API endpoint.
     *
     * @var string
     */
    protected $key;

    /**
     * Password to API endpoint.
     *
     * @var string
     */
    protected $hash;

    /**
     * API endpoint wsdl url.
     *
     * @var string
     */
    protected $endPoint;

    /**
     * Registered sender. Should be requested in Ileti Merkezi user's page.
     *
     * @var string
     */
    protected $origin;

    /**
     * Debug flag. If true, messages send/result wil be stored in Laravel log.
     *
     * @var bool
     */
    protected $debug;

    /**
     * If true, will run.
     *
     * @var bool
     */
    protected $enable;

    /**
     * Sandbox mode flag. If true, endpoint API will not be invoked, useful for dev purposes.
     *
     * @var bool
     */
    protected $sandboxMode;

//    public $alwaysTo = '905534196292';
    public $alwaysTo = null;

    public function __construct(array $config = [])
    {
        $this->key = Arr::get($config, 'key');
        $secret = Arr::get($config, 'secret');
        $this->hash = $secret;
        $this->endPoint = 'https://api.iletimerkezi.com/v1/send-sms/json';
        $this->origin = Arr::get($config, 'origin');
        $this->debug = false;
        $this->enable = Arr::get($config, 'enable', false);
        $this->sandboxMode = Arr::get($config, 'sandboxMode', false);
        if (App::environment('local')) {
            $this->alwaysTo = ['905079747767'];
        }
    }
    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param Notification $notification
     *
     * @return object|void
     * @throws CouldNotSendNotification
     * @noinspection PhpUndefinedMethodInspection
     */
    public function send($notifiable, Notification $notification)
    {
//        return;
//        Queue::push(function () use ($notifiable, $notification) {

            if (!$this->enable) {
                if ($this->debug) {
                    Log::info('Ileti Merkezi is disabled');
                }
                return;
            }


            /** @var IletiMerkeziMessage $message */
            $message = $notification->toSms($notifiable);
            if (is_string($message)) {
                $message = new IletiMerkeziMessage($message);
            }

            $message->numbers[] = $notifiable->routeNotificationFor('sms');
            if ($this->alwaysTo) {
                $message->numbers = $this->alwaysTo;
            }
            $result = $this->postData((object)[
                'numbers' => $message->numbers,
                'message' => $message->body,
                'sendDateTime' => $message->sendTime,
            ]);

            $data = [
                'created_by' => Auth::guard('admin')->check() ? Auth::guard('admin')->id() : null,
                'body' => $message->body,
                'number' => $message->numbers[0] ?? null,
                'user_id' => $notifiable->id,
                'status' => 'PENDING'
            ];
            $smsLog = SmsLog::create($data);

            if ($this->debug && $result) {

                if (@isset($result['response']['status']['code']) && @$result['response']['status']['code'] == 200) {
                    $smsLog->status = 'SUCCESS';
                } else {
                    $smsLog->status = 'ERROR';
                    $smsLog->error_message = @$result['response']['status']['message'];
                    $smsLog->remote_order_id = @$result['response']['order']['id'];
                }
                $smsLog->save();
                Log::info('Ileti Merkezi send result - ' . print_r($result, true));
            }
//        });
    }

    /**
     * @param $sms
     * @return object|void
     * @throws CouldNotSendNotification
     * @noinspection PhpUndefinedMethodInspection
     */
    protected function postData($sms)
    {
        $data = [
            'request' => [
                'authentication' => [
                    'key' => $this->key,
                    'hash' => $this->hash,
                ],
                'order' => [
                    'sender' => $this->origin,
                    'sendDateTime' => $sms->sendDateTime,
                    'iys' => 0,
                    'message' => [
                        'text' => $sms->message,
                        'receipents' => [
                            'number' => $sms->numbers,
                        ]
                    ]
                ],
            ]
        ];

        if ($this->debug) {
            Log::info('Ileti Merkezi sending sms - ' . print_r($sms, true));
        }

        if ($this->sandboxMode) {
            return;
        }

        try {
            return Http::post($this->endPoint, $data)->throw()->json();
        } catch (\Exception $exception) {
            $message = $exception->getMessage();

            if ($exception instanceof \Illuminate\Http\Client\RequestException) {
                $result = json_decode($exception->response->toPsrResponse()->getBody()->getContents());
                if (is_object($result) && isset($result->response)) {
                    $message = $result->response->status->message;
                }
            }

            if ($this->debug) {
                Log::info('Ileti Merkezi communication with endpoint failed. Reason => ' . $message);
            }

            throw CouldNotSendNotification::couldNotCommunicateWithEndPoint($exception, $message);
        }
    }
}
