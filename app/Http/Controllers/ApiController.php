<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Library\Logger;
use App\Services\LocaltonetService;
use App\Traits\AjaxResponses;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;


class ApiController extends Controller
{
    use AjaxResponses;

    protected $localtonetService;

    public function __construct(LocaltonetService $localtonetService)
    {
        $this->localtonetService = $localtonetService;
    }

    public function getAuthTokens(Request $request)
    {
        $data = [];
        $auth_tokens = $this->localtonetService->getAuthTokens();

        if (!($auth_tokens && $auth_tokens['hasError'] === false && $auth_tokens['result'])) {
            return response()->json([
                'success' => false,
                'message' => 'Auth token bağlantı hatası. ' . ($auth_tokens['errors'] ? json_encode($auth_tokens['errors']) : ''),
            ]);
        }

        return [
            'success' => true,
            'tokens' => $auth_tokens['result']
        ];
    }

    private function generatePassword()
    {
        $a = 'abcdefghijklmnopqrstuvwxyz';
        $b = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $c = '0123456789';

        $result = '';

        for ($i = 0; $i < 3; $i++) {
            $result .= $a[mt_rand(0, strlen($a) - 1)];
        }

        for ($i = 0; $i < 3; $i++) {
            $result .= $b[mt_rand(0, strlen($b) - 1)];
        }

        for ($i = 0; $i < 3; $i++) {
            $result .= $c[mt_rand(0, strlen($c) - 1)];
        }

        return $result;
    }


    public function createTunnel(Request $request)
    {

        //

        $serverCode = "tr1";

//        return ['sadfasdfs'];
        $quota = $request->quota; // MB
        $protocol_type = $request->protocol_type ?: 'http';
        $auth_token = $request->auth_token;

        if (!$quota || !$protocol_type) {
            return $this->errorResponse('Parametre hatası.');
        }

        if (!$auth_token)
            return $this->errorResponse('Token hatası.');

        $protocol_type = $protocol_type == 'http' ? 6 : 7;


        $title = $request->title;

        $username = $request->username ?: $this->generatePassword();
        $password = $request->password ?: $this->generatePassword();


        $access_control_list = (array)$request->acl_list;
        $result = $this->localtonetService->createProxyTunnelWithDetails($title, $protocol_type, $serverCode, $auth_token, $quota, $access_control_list, $username, $password);

        if (@$result['hasError'] === false) {
            $tunnel_id = $result['result']['tunnelId'] ?? null;
            if (!$tunnel_id)
                return $this->errorResponse('Error #12300097');

            $start_tunnel = $this->localtonetService->startTunnel($tunnel_id);

            if (@$start_tunnel['hasError'] === false) {
                $getTunnelDetail = $this->localtonetService->getTunnelDetail($tunnel_id);

                if ($getTunnelDetail['hasError'] === true) {
                    return $this->errorResponse('Error #12300098');
                }

                $data = $getTunnelDetail['result'] ?? [];

                $serverIp = $data['serverIp'] ?? null;
                $serverPort = $data['serverPort'] ?? null;
                $username = $data['authenticationUsername'] ?? null;
                $password = $data['authenticationPassword'] ?? null;


//                $tunnel_details_full = $this->getTunnelDetails($request,$tunnel_id);
                return [
                    'success' => true,
                    'data' => [
                        'tunnel_id' => $tunnel_id,
                        'server_ip' => $serverIp,
                        'server_port' => $serverPort,
                        'username' => $username,
                        'password' => $password,
                        'auth_info' => base64_encode($serverIp . ':' . $serverPort . ':' . $username . ':' . $password)
                    ]
                ];
            }
            return [
                'xxx123123', $start_tunnel
            ];
        }

        return ['xxx321321', $result];
    }

    public function getTunnelDetails(Request $request, $tunnel_id = null)
    {
        $tunnel_id = $tunnel_id ? $tunnel_id : $request->tunnel_id;
//        Cache::forget('LOCALTONET_PR_DATA_' . $tunnel_id);
        $localtonet_proxy_data = Cache::remember('LOCALTONET_PR_DATA_' . $tunnel_id, 90, function () use ($tunnel_id) {
            $service = new LocaltonetService();
//      $service->setBandwidthLimitForTunnel('534302','5120','3');
            $proxy = $service->getTunnelDetail($tunnel_id);
            if (@$proxy["hasError"] || !isset($proxy["result"]) || @$proxy["result"]["id"] == 0) {
                Logger::error("LOCALTONET_GET_TUNNEL_DETAIL_ERROR", ["errorCode" => @$proxy["errorCode"], "errors" => @$proxy["errors"]]);
                return false;
            }

            if (isset($proxy["result"]["bandwidthLimit"]) && is_numeric($proxy["result"]["bandwidthLimit"])) {
                if ($proxy["result"]["bandwidthLimit"] > 0) {
                    $bandwidthLimit = $proxy["result"]["bandwidthLimit"];
                } else {
                    $bandwidthLimit = "unlimited";
                }
            } else {
                $bandwidthLimit = 0;
            }

            $proxy["result"]["bandwidthLimit"] = $bandwidthLimit;
            $proxy["result"]["bandwidthUsage"] = isset($proxy["result"]["bandwidthUsage"]) && is_numeric($proxy["result"]["bandwidthUsage"]) ? $proxy["result"]["bandwidthUsage"] : @$proxy["result"]["bandwidthUsage"];
            //START::Usage Limit Info

            if ($proxy["result"]["bandwidthLimit"] == 0 || $proxy["result"]["bandwidthLimit"] == "unlimited") {
                $proxy["result"]["bgBandwidthUsage"] = null;
            } else {
                $usagePercentage = ($proxy["result"]["bandwidthUsage"] / $proxy["result"]["bandwidthLimit"]) * 100;
                $bg = null;
                if ($usagePercentage == 100) $bg = "danger";
                else if ($usagePercentage >= 60) $bg = "warning";
                $proxy["result"]["bgBandwidthUsage"] = $bg;
            }
            //END::Usage Limit Info


            //START::get authentication data
            $getAuthentication = $service->getAuthenticationDataByTunnelId($tunnel_id);

            if ($getAuthentication["hasError"]) {
                Logger::error("LOCALTONET_GET_AUTHENTICATION_DATA_ERROR", ["order_id" => $this->id, "errorCode" => @$getAuthentication["errorCode"], "errors" => @$getAuthentication["errors"]]);
            } else {
                $getAuthenticationResult = $getAuthentication["result"] ?? null;
                $proxy["result"]["authentication"]["isActive"] = $getAuthenticationResult["isActive"] ?? null;
                $proxy["result"]["authentication"]["userName"] = $getAuthenticationResult["userName"] ?? null;
                $proxy["result"]["authentication"]["password"] = $getAuthenticationResult["password"] ?? null;

//                $product_info = $this->product_info;
//
//                $product_info['authentication'] = [
//                    'ip' => $proxy["result"]['serverIp'] ?? '',
//                    'port' => $proxy["result"]['serverPort'] ?? '',
//                    'username' => $proxy["result"]["authentication"]["userName"],
//                    'password' => $proxy["result"]["authentication"]["password"]
//                ];
//
//                $this->update([
//                    'product_info' => $product_info
//                ]);

            }
            //END::get authentication data

            if ($proxy["result"]["protocolType"] == "ProxyHttp") $proxy["result"]["drawProtocolType"] = "Http / Http(s)";
            else if ($proxy["result"]["protocolType"] == "ProxySocks") $proxy["result"]["drawProtocolType"] = "Socks5";
            else $proxy["result"]["drawProtocolType"] = "-";

            if ($proxy["result"]["authentication"]['isActive']) {
                $proxy["result"]["drawProxy"] = @$proxy["result"]["serverIp"] . ":" . @$proxy["result"]["serverPort"] . ":" . @$proxy["result"]["authentication"]["userName"] . ":" . @$proxy["result"]["authentication"]["password"];
            } else {
                $proxy["result"]["drawProxy"] = @$proxy["result"]["serverIp"] . ":" . @$proxy["result"]["serverPort"];
            }

            //START::Airplane Mode
            $airplaneMode = $service->getAirplaneModeSettings(@$proxy["result"]["authToken"]);
            if (@$airplaneMode["hasError"]) {
                Logger::error("LOCALTONET_GET_AIRPLANE_MODE_SETTINGS_ERROR", ["order_id" => $this->id, "errorCode" => @$airplaneMode["errorCode"], "errors" => @$airplaneMode["errors"]]);
            }
            $proxy["result"]["airplaneMode"] = @$airplaneMode["result"];
            //END::Airplane Mode

            return !isset($proxy["result"]) || @$proxy["result"]["id"] == 0 ? [] : $proxy;
        });


        if ($localtonet_proxy_data && $localtonet_proxy_data['hasError'] === false) {
            return [
                'success' => true,
                'data' => $localtonet_proxy_data['result']
            ];
        }
        return [
            'success' => false,
            'data' => [],
            'message' => 'LPD not found. #1239990'
        ];
    }

    public function getIpHistory(Request $request, $tunnel_id = null)
    {
        $tunnel_id = $tunnel_id ? $tunnel_id : $request->tunnel_id;

        if (!$tunnel_id) {
            return [
                'success' => false,
                'message' => 'Tunnel ID not found.'
            ];
        }

        $tunnel = $this->getTunnelDetails($request, $tunnel_id);
        if ($tunnel['success'] === false) {
            return [
                'success' => false,
                'message' => 'Tunnel is not found.'
            ];
        }
        $tunnel = $tunnel['data'];
        $service = $this->localtonetService;
        $ipHistory = [];

        $cache_key = 'XXLOCALTONET_PR_IP_HISTORY_' . $tunnel_id;

        if (Cache::has($cache_key)) {
            $getIpHistoryResult = Cache::get($cache_key);
        } else {
            $getIpHistoryResult = $service->getIpHistoryByAuthToken($tunnel["authToken"]);
            Cache::put($cache_key, $getIpHistoryResult, 3);
        }
//        $getIpHistoryResult = Cache::remember('LOCALTONET_PR_IP_HISTORY_' . $tunnel_id, 1, function () use ($service, $tunnel) {
//            return $service->getIpHistoryByAuthToken($tunnel["authToken"]);
//        });

        if (@$getIpHistoryResult["hasError"]) {
            Logger::error("LOCALTONET_GET_IP_HISTORY_BY_AUTH_TOKEN_ERROR", ["tunnel_id" => $tunnel_id, "errorCode" => @$getIpHistoryResult["errorCode"], "errors" => @$getIpHistoryResult["errors"]]);
            return [
                'success' => false,
                'data' => $getIpHistoryResult
            ];
        } else {
            $getIpHistoryResult = $getIpHistoryResult["result"] ?? null;
            $ipHistory = $getIpHistoryResult;
            if (!empty($ipHistory)) {
                $proxyCreateDate = Carbon::parse(@$tunnel["createDate"])->toDateTimeString();
                foreach ($ipHistory as $index => $history) {
                    $historyDate = Carbon::parse($history["date"])->toDateTimeString();

                    if ($historyDate < $proxyCreateDate) {
                        unset($ipHistory[$index]);
                        continue;
                    }

                    if (!empty($history['date'])) {
                        $ipHistory[$index]['date'] = formatDateTimeInAppTimezone($history['date']);
                    }
                }
            }
        }
        return [
            'success' => true,
            'data' => $ipHistory
        ];
    }

    public function getTunnelManager(Request $request)
    {
        $pp_id = $request->id;
        $pp_token = $request->token;

        if (!$this->validateTokenId($pp_id, $pp_token))
            return 'Validation Error';

//        $tunnel = $this->getTunnelDetails($request, $pp_id);
//        $tunnel = json_decode(json_encode($tunnel['data'])) ?? [];
//        if (!$tunnel) return 'Bir hata meydana geldi. Hata Kodu: #1239991   ';
        return view('proxy-manager', compact('pp_id', 'pp_token'));
        return 'hhh';
    }

    public function proxyManagerActions(Request $request)
    {
        $pp_id = $request->id;
        $pp_token = $request->token;

        if (!$this->validateTokenId($pp_id, $pp_token))
            return 'Validation Error';

        $action = $request->action;

        switch ($action) {
            case 'general_information':
                $tunnel = $this->getTunnelDetails($request, $pp_id);
                if (!$tunnel || !$tunnel['data']) {
                    return [
                        'success' => false,
                        'message' => 'Bir hata meydana geldi. Hata Kodu: #1239992',
                        'data' => []
                    ];
                }

                $tunnel = json_decode(json_encode($tunnel['data'])) ?? [];

                return [
                    'success' => true,
                    'data' => $tunnel
                ];
                break;

            case 'get_ip_history':


                return $this->getIpHistory($request, $pp_id);

                break;

            case 'save_ip_settings':

                $tunnel = $this->getTunnelDetails($request, $pp_id);
                $tunnel_data = $tunnel['data'];

                $authToken = $tunnel_data['authToken'] ?? '';

                if (!$authToken) {
                    return [
                        'success' => false,
                        'message' => 'A.T. not found.'
                    ];
                }


                if ($request->is_active == 'on' && $request->seconds < 30) {
                    return [
                        'success' => false,
                        'message' => 'Yenileme süresi en az 30 saniye olmalıdır.'
                    ];
                }


                $result = $this->localtonetService->setAutoAirplaneModeSetting($authToken, $request->is_active == 'on', (int)$request->seconds);

                if ($result && $result['hasError'] === false) {
                    Cache::forget('LOCALTONET_PR_DATA_' . $pp_id);
                    return [
                        'success' => true,
                        'message' => 'Değişiklikler kaydedildi.'
                    ];
                }
                return [
                    'success' => false,
                    'message' => ($result && @$result['errors'] ? $result->errors[0] : 'Bir hata meydana geldi. #2300008')
                ];

                break;

            case 'save_auth_settings':


                $result = $this->localtonetService->setAuthenticationForTunnel($pp_id, $request->is_active == 'on', $request->username, $request->password);

                if ($result && $result['hasError'] === false) {
                    Cache::forget('LOCALTONET_PR_DATA_' . $pp_id);
                    return [
                        'success' => true,
                        'message' => 'Değişiklikler başarıyla kaydedildi.'
                    ];
                }
                return [
                    'success' => false,
                    'message' => ($result && @$result['errors'] ? $result->errors[0] : 'Bir hata meydana geldi. #2300008')
                ];

                break;

            case 'change_status':
                if ($request->status == 1){
                    $result = $this->localtonetService->startTunnel($pp_id);
                }else{
                    $result = $this->localtonetService->stopTunnel($pp_id);
                }

                if ($result && $result['hasError'] === false) {
                    Cache::forget('LOCALTONET_PR_DATA_' . $pp_id);
                    return [
                        'success' => true,
                        'message' => 'İşlem başarılı.'
                    ];
                }
                return [
                    'success' => false,
                    'message' => 'İşlem tamamlanırken bir hata oluştu. '.(@$result['errors'] ? @$result['errors'][0] : '')
                ];
                break;

            case 'delete_proxy':
                $result = $this->localtonetService->deleteTunnel($pp_id);
                if ($result && @$result['hasError'] === false) {
                    Cache::forget('LOCALTONET_PR_DATA_' . $pp_id);
                    return [
                        'success' => true,
                        'message' => 'Proxy başarıyla silindi.'
                    ];
                }
                return [
                    'success' => false,
                    'message' => 'Proxy silinirken hata oluştu. ' . (@$result['errors'] ? @$result['errors'][0] : '')
                ];
                break;

            default:

                return $request->all();
                break;
        }
        $tunnel = $this->getTunnelDetails($request, $pp_id);
        $tunnel = json_decode(json_encode($tunnel['data'])) ?? [];
    }

    public function setExpirationDateForTunnel(Request $request)
    {
        $tunnelId = $request->tunnel_id;
        $dueDate = $request->due_date;

        if (!$tunnelId || !$dueDate) {
            return $this->errorResponse('tunnel_id ve due_date parametreleri gereklidir.');
        }

        $result = $this->localtonetService->setExpirationDateForTunnel($tunnelId, $dueDate);

        if ($result && @$result['hasError'] === false) {
            return [
                'success' => true,
                'message' => 'Süre başarıyla güncellendi.',
                'data' => $result['result'] ?? null,
            ];
        }

        return [
            'success' => false,
            'message' => 'Süre güncellenirken hata oluştu.',
            'errors' => $result['errors'] ?? [],
        ];
    }

    public function updateIpChangeLink(Request $request)
    {
        $tunnelId = $request->tunnel_id;
        $ipChangeLink = $request->ip_change_link;

        if (!$tunnelId) {
            return $this->errorResponse('tunnel_id parametresi gereklidir.');
        }

        return [
            'success' => true,
            'message' => 'IP change link güncellendi.',
            'data' => [
                'tunnel_id' => $tunnelId,
                'ip_change_link' => $ipChangeLink,
            ],
        ];
    }

    private function validateTokenId($id, $token)
    {
        $validate_token = base64_encode(base64_encode(sha1(md5(sha1(intval($id) * 2901 + 199 . 'NETPUS2020**asd')))));

        if ($token != $validate_token)
            return false;
        return true;
    }
}
