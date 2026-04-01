<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\OrderLocaltonet\AuthenticationRequest;
use App\Library\Logger;
use App\Models\Order;
use App\Traits\AjaxResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class OrderLocaltonetController extends Controller
{
    use AjaxResponses;

    public function getProxy($order, ?int $tunnelId = null)
    {
        $pi = $order->product_info ?? [];
        $hasProxy = isset($pi['proxy_id']) || ! empty($pi['localtonet_v4_proxy_ids'] ?? []);
        if (! $hasProxy) {
            return $this->errorResponse('Proxy bulunamadı.'.__('refresh_the_page_and_try_again'));
        }

        $tid = $tunnelId ?? (int) ($order->getLocaltonetProxyId() ?? 0);
        if ($tid <= 0) {
            return $this->errorResponse('Proxy bulunamadı.'.__('refresh_the_page_and_try_again'));
        }

        if ($order->isCanDeliveryType('LOCALTONETV4') && ! $order->orderOwnsLocaltonetTunnelId($tid)) {
            return $this->errorResponse('Geçersiz tünel.');
        }

        $stored = $order->getStoredTunnelDetail($tid);
        if ($stored !== null) {
            return $stored;
        }

        $proxy = $order->getLocaltonetTunnelDetailCached($tid);
        if (! $proxy || empty($proxy['result'])) {
            Logger::error('LOCALTONET_GET_PROXY_STOP_TUNNEL_ERROR', ['order_id' => $order->id, 'tunnel_id' => $tid]);

            return [
                'hasError' => true,
                'errorMessage' => 'Proxy bulunamadı. Lütfen destek talebi üzerinden iletişme geçiniz.',
            ];
        }

        return $proxy['result'] ?? [];
    }

    public function getProxyListTable(Request $request)
    {
        $dataOrder = $request->dataOrder;
        if (!$dataOrder) return $this->errorResponse("Geçersiz parametre");

        $order = Order::whereUserId(Auth::id())->orderBy('id', 'desc')->skip($dataOrder - 1)->first();

        $data = null;
        if ($order && ($order->status == "ACTIVE" || $order->status == "PASSIVE")) {
            if ($order->isLocaltonetLikeDelivery()) {
                $proxy = $order->getProxyLocaltonet();
                if ($proxy) {
                    $proxy = $proxy["result"] ?? [];

                    if (@$proxy["bandwidthLimit"] == "unlimited") {
                        $traffic = convertByteToGB(@$proxy["bandwidthUsage"]) . " / " . __("unlimited");
                    } else {
                        $traffic = convertByteToGB(@$proxy["bandwidthUsage"]) . " / " . convertByteToGB(@$proxy["bandwidthLimit"]) . " GB";
                    }
                    $ipHistory = $order->getLocaltonetIpHistory();
                    $data = [
                        "deliveryType" => $order->product_data["delivery_type"] ?? "LOCALTONET",
                        "orderId" => $order->id,
                        "ipPort" => @$proxy["drawProxy"],
                        "isp_image" => $order?->product?->isp_image ? asset($order->product->isp_image) : null,
                        "ip" => isset($ipHistory) && is_array($ipHistory) ? $ipHistory[0]["ip"] ?? "-" : "-",
                        "ip_change_url" => route("portal.orders.localtonet.ipChangePost", ["order" => $order->id, "t" => base64_encode(@$proxy["airplaneMode"]["ipChangeLink"]["linkToken"])]),
                        "status" => @$proxy["status"],
                        "drawStatus" => @$proxy["status"] ? "<span class='badge badge-success'>" . __("active") . "</span>" : "<span class='badge badge-danger'>" . __("passive") . "</span>",
                        "traffic" => $traffic,
                        "viewUrl" => route("portal.orders.show", ["order" => $order->id])
                    ];




                }
            } elseif ($order->isCanDeliveryType('LOCALTONET_ROTATING')) {
                $pi = $order->product_info ?? [];
                $di = $order->product_data['delivery_items'] ?? [];
                $lrHost = $pi['lr_host'] ?? ($di['host'] ?? '-');
                $lrPort = $pi['lr_port'] ?? ($di['port'] ?? '-');
                $lrUser = $pi['lr_username'] ?? '-';
                $lrPass = $pi['lr_password'] ?? '-';

                $ipPortStr = $lrHost . ':' . $lrPort . ':' . $lrUser . ':' . $lrPass;

                $traffic = '-';
                $poolId = (int) ($pi['lr_pool_id'] ?? 0);
                $clients = $pi['lr_clients'] ?? [];
                $pool = \App\Models\LocaltonetRotatingPool::find($poolId);

                if ($pool && $pool->api_key && count($clients) > 0) {
                    $svc = new \App\Services\LocaltonetService(null, $pool->api_key);
                    $tid = (int) ($clients[0]['tunnel_id'] ?? 0);
                    $cid = $clients[0]['client_id'] ?? '';
                    $listRes = $svc->listSharedProxyClients($tid);
                    $allC = $listRes['result'] ?? [];

                    $myC = null;
                    if (is_array($allC)) {
                        foreach ($allC as $cc) {
                            if (($cc['id'] ?? $cc['Id'] ?? null) === $cid) {
                                $myC = $cc;
                                break;
                            }
                        }
                    }

                    if ($myC) {
                        $bwRaw = $myC['bandwidthLimit'] ?? $myC['BandwidthLimit'] ?? 0;
                        $usRaw = $myC['bandwidthUsage'] ?? $myC['BandwidthUsage'] ?? 0;
                        $dstRaw = $myC['dataSizeType'] ?? $myC['DataSizeType'] ?? null;

                        $bwVal = is_numeric($bwRaw) ? (float) $bwRaw : 0;
                        $usVal = is_numeric($usRaw) ? (float) $usRaw : 0;

                        if ($dstRaw !== null && is_numeric($dstRaw) && (int) $dstRaw > 1) {
                            $mult = (float) $dstRaw;
                        } else {
                            $fst = (int) ($di['quota']['data_size_type'] ?? 4);
                            $mult = match ($fst) { 1 => 1, 2 => 1024, 3 => 1048576, 4 => 1073741824, 5 => 1099511627776, default => 1073741824 };
                        }

                        $bwBytes = $bwVal * $mult;
                        $usBytes = $usVal * $mult;

                        if ($pool->type === 'unlimited' || $bwBytes <= 0) {
                            $traffic = convertByteToGB($usBytes) . ' GB / ' . __('unlimited');
                        } else {
                            $traffic = convertByteToGB($usBytes) . ' / ' . convertByteToGB($bwBytes) . ' GB';
                        }
                    }
                }

                $data = [
                    "deliveryType" => "LOCALTONET_ROTATING",
                    "orderId" => $order->id,
                    "ipPort" => $ipPortStr,
                    "isp_image" => $order?->product?->isp_image ? asset($order->product->isp_image) : null,
                    "ip" => "-",
                    "drawStatus" => $order->status == "ACTIVE" ? "<span class='badge badge-success'>" . __("active") . "</span>" : "<span class='badge badge-danger'>" . __("passive") . "</span>",
                    "traffic" => $traffic,
                    "viewUrl" => route("portal.orders.show", ["order" => $order->id])
                ];
            } elseif ($order->isThreeProxyDelivery()) {
                $tpList = $order->getThreeProxyDisplayList();
                $lines = [];
                foreach (array_slice($tpList, 0, 2) as $tp) {
                    $lines[] = ($tp['ip'] ?? '') . ':' . ($tp['http_port'] ?? '') . ':' . ($tp['username'] ?? '') . ':' . ($tp['password'] ?? '');
                }
                $data = [
                    "deliveryType" => "THREEPROXY",
                    "orderId" => $order->id,
                    "ipPort" => implode("<br> ", $lines),
                    'isSeeMore' => count($tpList) > 2,
                    "isp_image" => $order?->product?->isp_image ? asset($order->product->isp_image) : null,
                    "ip" => "-",
                    "status" => "-",
                    "drawStatus" => $order->status == "ACTIVE" ? "<span class='badge badge-success'>" . __("active") . "</span>" : "<span class='badge badge-danger'>" . __("passive") . "</span>",
                    "traffic" => "-",
                    "viewUrl" => route("portal.orders.show", ["order" => $order->id])
                ];
            } else {
                $proxyList = $order->proxyList;
                if (!is_array($proxyList)) {
                    $proxyList = [];
                }
                $data = [
                    "deliveryType" => "STACK",
                    "orderId" => $order->id,
                    "ipPort" => implode("<br> ", array_slice($proxyList, 0, 2)),
                    'isSeeMore' => count($proxyList) > 2,
                    "isp_image" => $order?->product?->isp_image ? asset($order->product->isp_image) : null,
                    "ip" => "-",
                    "status" => "-",
                    "drawStatus" => $order->status == "ACTIVE" ? "<span class='badge badge-success'>" . __("active") . "</span>" : "<span class='badge badge-danger'>" . __("passive") . "</span>",
                    "traffic" => "-",
                    "viewUrl" => route("portal.orders.show", ["order" => $order->id])
                ];
            }
        }

        return $this->successResponse("", ["data" => $data]);
    }

    public function start(Order $order)
    {
        $service = $order->resolveLocaltonetService();
        $proxy = $this->getProxy($order);
        if (@$proxy["hasError"]) {
            return $this->errorResponse($proxy["errorMessage"]);
        }

        if ($proxy["status"] == 0) {
            $response = $service->startTunnel($proxy["id"]);
            if (@$response["hasError"]) {
                Logger::error("LOCALTONET_START_TUNNEL_ERROR", ["order_id" => $order->id, "errorCode" => @$response["errorCode"], "errors" => @$response["errors"]]);
                return $this->errorResponse(__("error_response"));
            }

            $updateTitle = $service->updateTitle($proxy["id"], $order->createProxyTitle());
            if (@$updateTitle["hasError"]) {
                Logger::error("LOCALTONET_UPDATE_TITLE_ERROR", ["order_id" => $order->id, "errorCode" => @$updateTitle["errorCode"], "errors" => @$updateTitle["errors"]]);
                return $this->errorResponse(__("error_response"));
            }

            $order->refreshSingleTunnelInDb((int) $proxy["id"]);
        }

        return $this->successResponse(__("changes_saved_successfully"));
    }

    public function stop(Order $order)
    {
        $service = $order->resolveLocaltonetService();
        $proxy = $this->getProxy($order);
        if (@$proxy["hasError"]) {
            return $this->errorResponse($proxy["errorMessage"]);
        }

        if ($proxy["status"] == 1) {
            $response = $service->stopTunnel($proxy["id"]);
            if (@$response["hasError"]) {
                Logger::error("2LOCALTONET_STOP_TUNNEL_ERROR", ["order_id" => $order->id, "errorCode" => @$response["errorCode"], "errors" => @$response["errors"]]);
                return $this->errorResponse(__("error_response"));
            }

            $updateTitle = $service->updateTitle($proxy["id"], "(MD!) " . $proxy["title"]);
            if (@$updateTitle["hasError"]) {
                Logger::error("LOCALTONET_UPDATE_TITLE_ERROR", ["order_id" => $order->id, "errorCode" => @$updateTitle["errorCode"], "errors" => @$updateTitle["errors"]]);
                return $this->errorResponse(__("error_response"));
            }

            $order->refreshSingleTunnelInDb((int) $proxy["id"]);
        }
        return $this->successResponse(__("changes_saved_successfully"));
    }

    public function authentication(AuthenticationRequest $request, Order $order)
    {
        if ($order->isCanDeliveryType('LOCALTONETV4')) {
            return $this->authenticationV4($request, $order);
        }

        $service = $order->resolveLocaltonetService();
        $proxy = $this->getProxy($order);
        if (@$proxy["hasError"]) {
            return $this->errorResponse($proxy["errorMessage"]);
        }

        if (isset($proxy["status"]) && $proxy["status"] == 1) {
            $stopTunnel = $service->stopTunnel($proxy["id"]);
            if (@$stopTunnel["hasError"]) {
                Logger::error("LOCALTONET_AUTHENTICATION_STOP_TUNNEL_ERROR", ["order_id" => $order->id, "errorCode" => @$stopTunnel["errorCode"], "errors" => @$stopTunnel["errors"]]);
                return $this->errorResponse(__("error_response"));
            }
        }

        $isActive = $request->is_active ? 1 : 0;

        $userName = $isActive ? $request->user_name : Str::random(6);
        $password = $isActive ? $request->password : Str::random(6);
        $setAuthentication = $service->setAuthenticationForTunnel($proxy["id"], $isActive, $userName, $password);
        if (@$setAuthentication["hasError"]) {
            Logger::error("LOCALTONET_AUTHENTICATION_SET_AUTHENTICATION_ERROR", ["order_id" => $order->id, "errorCode" => @$setAuthentication["errorCode"], "errors" => @$setAuthentication["errors"]]);
            return $this->errorResponse(__("error_response"));
        }

        $deleteAllIpRestrictionResult = $service->deleteAllIpRestrictions($proxy["id"]);
        if (@$deleteAllIpRestrictionResult["hasError"]) {
            Logger::error("LOCALTONET_DELETE_ALL_IP_RESTRICTION_ERROR", ["order_id" => $order->id, "errorCode" => @$deleteAllIpRestrictionResult["errorCode"], "errors" => @$deleteAllIpRestrictionResult["errors"]]);
        }

        if (! $isActive) {
            $whitelist = preg_split('/\R/u', (string) $request->whitelist) ?: [];
            foreach ($whitelist as $ip) {
                $ip = trim($ip);
                if ($ip === '') {
                    continue;
                }
                $addIpRestrictionResult = $service->addIpRestriction($proxy["id"], $ip);
                if (@$addIpRestrictionResult["hasError"]) {
                    Logger::error("LOCALTONET_ADD_IP_RESTRICTION_ERROR", ["order_id" => $order->id, "errorCode" => @$addIpRestrictionResult["errorCode"], "errors" => @$addIpRestrictionResult["errors"]]);
                }
            }

            $changeAllowIpRestriction = $service->updateIsAllowForIpRestriction($proxy["id"], true);
            if (@$changeAllowIpRestriction["hasError"]) {
                Logger::error("LOCALTONET_UPDATE_IS_ALLOW_FOR_IP__RESTRICTION_ERROR", ["order_id" => $order->id, "errorCode" => @$changeAllowIpRestriction["errorCode"], "errors" => @$changeAllowIpRestriction["errors"]]);
            }
        }

        $startTunnel = $service->startTunnel($proxy["id"]);
        if (@$startTunnel["hasError"]) {
            Logger::error("LOCALTONET_START_TUNNEL_ERROR_ORDER_LOCALTONET_AUTHENTICATION", ["order_id" => $order->id, "errorCode" => @$startTunnel["errorCode"], "errors" => @$startTunnel["errors"]]);
        }

        $order->refreshSingleTunnelInDb((int) $proxy["id"]);

        return $this->successResponse(__("changes_saved_successfully"));
    }

    private function authenticationV4(AuthenticationRequest $request, Order $order)
    {
        $service  = $order->resolveLocaltonetService();
        $allIds   = $order->getAllLocaltonetProxyIds();

        if (count($allIds) === 0) {
            return $this->errorResponse('Siparişe ait tünel bulunamadı.');
        }

        $isActive = $request->is_active ? 1 : 0;
        $userName = $isActive ? $request->user_name : Str::random(6);
        $password = $isActive ? $request->password : Str::random(6);

        $intIds = array_map('intval', $allIds);
        $stopRes = $service->bulkStopTunnelsV2($intIds);
        if (! empty($stopRes['hasError'])) {
            Logger::warning('LOCALTONET_V4_AUTH_BULK_STOP_FAIL', [
                'order_id' => $order->id,
                'errors'   => $stopRes['errors'] ?? [],
            ]);
        }

        $failCount = 0;
        foreach ($allIds as $tid) {
            $setAuth = $service->setAuthenticationForTunnel((int) $tid, $isActive, $userName, $password);
            if (! empty($setAuth['hasError'])) {
                Logger::error('LOCALTONET_V4_AUTH_SET_FAIL', [
                    'order_id'  => $order->id,
                    'tunnel_id' => $tid,
                    'errors'    => $setAuth['errors'] ?? [],
                ]);
                $failCount++;
            }
        }

        if (! $isActive) {
            $whitelist = preg_split('/\R/u', (string) $request->whitelist) ?: [];
            $whitelist = array_values(array_filter(array_map('trim', $whitelist)));

            foreach ($allIds as $tid) {
                $service->deleteAllIpRestrictions((int) $tid);
                foreach ($whitelist as $ip) {
                    $service->addIpRestriction((int) $tid, $ip);
                }
                if (count($whitelist) > 0) {
                    $service->updateIsAllowForIpRestriction((int) $tid, true);
                }
            }
        }

        $pi = is_array($order->product_info) ? $order->product_info : [];
        $creds = $pi['localtonet_v4_auth_credentials'] ?? [];
        foreach ($allIds as $tid) {
            $creds[(int) $tid] = [
                'isActive' => (bool) $isActive,
                'userName' => $userName,
                'password' => $password,
            ];
        }
        $pi['localtonet_v4_auth_credentials'] = $creds;
        $order->product_info = $pi;
        $order->save();

        $startRes = $service->bulkStartTunnelsV2($intIds);
        if (! empty($startRes['hasError'])) {
            Logger::warning('LOCALTONET_V4_AUTH_BULK_START_FAIL', [
                'order_id' => $order->id,
                'errors'   => $startRes['errors'] ?? [],
            ]);
        }

        try {
            $order->fetchAndPersistAllTunnelDetails();
        } catch (\Throwable $e) {
            Logger::warning('LOCALTONET_V4_AUTH_DETAIL_REFRESH_FAIL', [
                'order_id' => $order->id,
                'msg'      => $e->getMessage(),
            ]);
        }

        if ($failCount > 0) {
            return $this->errorResponse($failCount . ' tünelde yetkilendirme güncellenemedi.');
        }

        return $this->successResponse(count($allIds) . ' proxy için yetkilendirme güncellendi.');
    }

    public function setServerPort(Request $request, Order $order)
    {
        $service = $order->resolveLocaltonetService();
        $request->validate([
            "server_port" => ['required', 'numeric', 'min:500', 'max:65536'],
        ], [
            'server_port.required' => __('custom_field_is_required', ['name' => "PORT"]),
            'server_port.min' => __('custom_field_is_min_size', ['name' => "PORT", 'size' => "500"]),
            'server_port.max' => __('custom_field_is_max_size', ['name' => "PORT", 'size' => "65536"]),
        ]);

        $proxy = $this->getProxy($order);
        if (@$proxy["hasError"]) {
            return $this->errorResponse("Proxy bulunamadı.");
        }

        $requestedPort = (int) $request->server_port;
        $currentPort = isset($proxy['serverPort']) ? (int) $proxy['serverPort'] : null;
        if ($currentPort !== null && $requestedPort === $currentPort) {
            return $this->errorResponse(
                'Bu port zaten proxyinizde atanmış. Farklı bir port kullanmak için lütfen mevcut porttan farklı bir numara girin (500–65535).'
            );
        }

        if (@$proxy["status"] == 0) {
            $startResult = $service->startTunnel($proxy["id"]);
            if (@$startResult["hasError"]) {
                Logger::error("LOCALTONET_START_TUNNEL_ERROR", ["order_id" => $order->id, "errorCode" => @$startResult["errorCode"], "errors" => @$startResult["errors"]]);
                return $this->errorResponse(__("error_response"));
            }
        }
        if (!$proxy["isReserved"]) {
            $changeReservedResult = $service->changePortReservedStatus($proxy["id"], true);
            if (@$changeReservedResult["hasError"]) {
                Logger::error("LOCALTONET_CHANGE_PORT_RESERVED_STATUS_ERROR", ["order_id" => $order->id, "errorCode" => @$changeReservedResult["errorCode"], "errors" => @$changeReservedResult["errors"]]);
                return $this->errorResponse(__("error_response"));
            }
        }

        $stopResult = $service->stopTunnel($proxy["id"]);
        if (@$stopResult["hasError"]) {
            Logger::error("3LOCALTONET_STOP_TUNNEL_ERROR", ["order_id" => $order->id, "errorCode" => @$stopResult["errorCode"], "errors" => @$stopResult["errors"]]);
            return $this->errorResponse(__("error_response"));
        }

        $response = $service->setServerPort($proxy["id"], $requestedPort);
        if (@$response["hasError"]) {
            Logger::error("LOCALTONET_SET_SERVER_PORT_ERROR", ["order_id" => $order->id, "errorCode" => @$response["errorCode"], "errors" => @$response["errors"]]);

            return $this->errorResponse($this->portChangeFailedMessage($requestedPort, $response));
        }
        if (! empty($response['result'])) {
            Logger::info('LOCALTONET_SET_SERVER_PORT_REJECTED', ['order_id' => $order->id, 'result' => $response['result']]);

            return $this->errorResponse($this->portChangeFailedMessage($requestedPort, $response));
        }

        $startResult = $service->startTunnel($proxy["id"]);
        if (@$startResult["hasError"]) {
            Logger::error("LOCALTONET_START_TUNNEL_ERROR", ["order_id" => $order->id, "errorCode" => @$startResult["errorCode"], "errors" => @$startResult["errors"]]);
        }

        $order->refreshSingleTunnelInDb((int) $proxy["id"]);

        return $this->successResponse(__("changes_saved_successfully"));
    }

    /**
     * Port dolu / rezerve / API reddi durumunda kullanıcıya gösterilecek metin.
     */
    private function portChangeFailedMessage(int $requestedPort, array $response): string
    {
        $hint = "Port {$requestedPort} atanamadı. Bu numara kullanımda, rezerve veya geçersiz olabilir. Lütfen 500–65535 aralığında farklı bir port deneyin.";
        $parts = [$hint];
        if (! empty($response['errors'])) {
            $e = $response['errors'];
            $parts[] = is_array($e) ? implode(' ', $e) : (string) $e;
        }
        if (! empty($response['result'])) {
            if (is_string($response['result'])) {
                $t = trim($response['result']);
                if ($t !== '') {
                    $parts[] = $t;
                }
            } else {
                $parts[] = json_encode($response['result'], JSON_UNESCAPED_UNICODE);
            }
        }

        return implode(' ', array_filter($parts));
    }

    public function setAutoAirplaneModeSetting(Request $request, Order $order)
    {
        $service = $order->resolveLocaltonetService();
        $proxy = $this->getProxy($order);
        if (@$proxy["hasError"]) {
            return $this->errorResponse($proxy["errorMessage"]);
        }

        if ($request->stop) {
            $airplaneMode = $service->setAutoAirplaneModeSetting($proxy["authToken"], false);
        } else {
            $request->validate([
                "time" => ['required', 'numeric', 'min:30']
            ], [
                'time.required' => __('custom_field_is_required', ['name' => __("duration")]),
                'time.min' => __('custom_field_is_min_size', ['name' => __("duration"), 'size' => "30"]),
            ]);

            $airplaneMode = $service->setAutoAirplaneModeSetting($proxy["authToken"], true, $request->time ?? 30);
        }
        if (@$airplaneMode["hasError"]) {
            return $this->errorResponse($proxy["errorMessage"]);
        }

        $order->refreshSingleTunnelInDb((int) $proxy["id"]);

        return $this->successResponse(__("changes_saved_successfully"));
    }

    public function ipChange(Request $request, Order $order)
    {
        $service = $order->resolveLocaltonetService();
        if (!$request->t) return $this->errorResponse("Geçersiz istek");
        if ($order->status != "ACTIVE") return $this->errorResponse("Hizmet durumunuz aktif olmadigi icin proxy bilgilerinde duzenleme yapilamaz. (Hizmet Durumu: " . __(mb_strtolower($order->status)) . ")");

        $response = $service->triggerAndroidChangeIp(base64_decode($request->t));

        return $response->json();
    }

    public function deviceRestart(Request $request, Order $order)
    {
        $service = $order->resolveLocaltonetService();
        if (!$request->t) return $this->errorResponse("Geçersiz istek");
        if ($order->status != "ACTIVE") return $this->errorResponse("Hizmet durumunuz aktif olmadigi icin proxy bilgilerinde duzenleme yapilamaz. (Hizmet Durumu: " . __(mb_strtolower($order->status)) . ")");
        $response = $service->triggerAndroidRestartPhone(base64_decode($request->t));

        return $response->json();
    }

    public function ipChangePost(Request $request, Order $order)
    {
        $service = $order->resolveLocaltonetService();
        if (!$request->t) return $this->errorResponse("Geçersiz istek");

        $response = $service->triggerAndroidChangeIp(base64_decode($request->t));
        $response = $response->json();


        if ($response["result"] == 1) {
            return $this->successResponse("IP değiştirme işlemi başarıyla tamamlandı.");
        }
        return $this->errorResponse("İstek sınırına ulaştınız. Lütfen daha sonra tekrar deneyin.");
    }

    public function deviceRestartPost(Request $request, Order $order)
    {
        $service = $order->resolveLocaltonetService();
        if (!$request->t) return $this->errorResponse("Geçersiz istek");

        $response = $service->triggerAndroidRestartPhone(base64_decode($request->t));
        $response = $response->json();
        if ($response["result"] == 1) {
            return $this->successResponse(
                'Cihaz yeniden başlatma isteği iletildi. Cihaz açıldıktan sonra proxy bağlantısı genellikle 5–10 dakika içinde tekrar kullanılabilir olur; bu sürede kısa kesintiler olabilir.'
            );
        }
        return $this->errorResponse(@$response["message"] . __("error_response"));
    }

    public function getIpHistory(Order $order)
    {
        $ipHistory = $order->getLocaltonetIpHistory();

        return $this->successResponse('', ['data' => is_array($ipHistory) ? $ipHistory : []]);
    }

    /**
     * IPv4 Localtonet: Localtonet v2 PATCH ile HTTP ↔ SOCKS5 protokol değişimi.
     */
    public function v4ToggleProtocol(Request $request, Order $order)
    {
        $access = $this->v4EnsureOrderAccess($order);
        if ($access instanceof JsonResponse) {
            return $access;
        }

        if (! $order->isCanDeliveryType('LOCALTONETV4')) {
            return $this->errorResponse('Bu işlem yalnızca IPv4 Localtonet siparişleri için geçerlidir.');
        }
        if (! $order->isLocaltonetPortalOperationsAllowed()) {
            return $this->errorResponse('Sipariş henüz teslim edilmedi.');
        }

        if ($request->boolean('bulk')) {
            return $this->v4ToggleProtocolBulk($order);
        }

        $inputTid = $request->input('tunnel_id');
        $tunnelId = ($inputTid !== null && $inputTid !== '') ? (int) $inputTid : null;

        $proxy = $this->getProxy($order, $tunnelId);
        if ($proxy instanceof JsonResponse) {
            return $proxy;
        }
        if (! is_array($proxy) || (! empty($proxy['hasError']))) {
            return $this->errorResponse($proxy['errorMessage'] ?? 'Proxy bilgisi alınamadı.');
        }

        $proxyId = $proxy['id'] ?? null;
        if (! $proxyId) {
            return $this->errorResponse('Tunnel bulunamadı.');
        }

        $isSocks = function_exists('localtonet_tunnel_result_is_socks') && localtonet_tunnel_result_is_socks($proxy);
        $httpCode = (int) config('services.localtonet_v4.v2_protocol_http', 6);
        $socksCode = (int) config('services.localtonet_v4.v2_protocol_socks', 7);
        $targetApiType = $isSocks ? $httpCode : $socksCode;
        $detailProtocolValue = $isSocks ? 'http' : 'socks5';

        $service = $order->resolveLocaltonetService();
        $patch = $service->patchTunnelProtocolType($proxyId, $targetApiType);
        if (! empty($patch['hasError'])) {
            $err = $patch['errors'] ?? [];
            $msg = is_array($err) ? implode(' ', $err) : (string) $err;

            return $this->errorResponse($msg !== '' ? $msg : 'Protokol güncellenemedi.');
        }

        Cache::forget('LOCALTONET_PR_DATA_'.$proxyId);
        $order->updateStoredTunnelProtocol($proxyId, $detailProtocolValue);
        $this->syncV4OrderDetailProtocolSelect($order, $detailProtocolValue);

        return $this->successResponse('Protokol güncellendi.');
    }

    /**
     * Çoklu IPv4 tünel: bulk API ile protokolü toplu değiştirir.
     */
    private function v4ToggleProtocolBulk(Order $order): JsonResponse
    {
        $ids = $order->getAllLocaltonetProxyIds();
        if (count($ids) === 0) {
            return $this->errorResponse('Tünel bulunamadı.');
        }

        $service = $order->resolveLocaltonetService();
        $httpCode  = (int) config('services.localtonet_v4.v2_protocol_http', 6);
        $socksCode = (int) config('services.localtonet_v4.v2_protocol_socks', 7);

        $firstProxy = $this->getProxy($order, (int) $ids[0]);
        $isSocks = false;
        if (is_array($firstProxy) && empty($firstProxy['hasError'])) {
            $isSocks = function_exists('localtonet_tunnel_result_is_socks') && localtonet_tunnel_result_is_socks($firstProxy);
        }

        $targetApiType       = $isSocks ? $httpCode : $socksCode;
        $detailProtocolValue = $isSocks ? 'http' : 'socks5';

        $bulkItems = [];
        foreach ($ids as $tid) {
            $bulkItems[] = [
                'tunnelId'     => (int) $tid,
                'protocolType' => $targetApiType,
            ];
        }

        $res = $service->patchTunnelsBulkProtocolType($bulkItems);

        if (! empty($res['hasError'])) {
            $err = $res['errors'] ?? [];
            $msg = is_array($err) ? implode(' ', $err) : (string) $err;
            Logger::error('LOCALTONET_V4_BULK_PROTOCOL_TOGGLE_FAIL', [
                'order_id' => $order->id,
                'target'   => $detailProtocolValue,
                'errors'   => $err,
            ]);

            return $this->errorResponse($msg !== '' ? $msg : 'Protokol güncellenemedi.');
        }

        foreach ($ids as $tid) {
            Cache::forget('LOCALTONET_PR_DATA_'.(int) $tid);
            $order->updateStoredTunnelProtocol((int) $tid, $detailProtocolValue);
        }

        $this->syncV4OrderDetailProtocolSelect($order, $detailProtocolValue);

        return $this->successResponse(
            count($ids) . ' tünelde protokol ' . strtoupper($detailProtocolValue) . ' olarak güncellendi.'
        );
    }

    private function syncV4OrderDetailProtocolSelect(Order $order, string $protocolValue): void
    {
        if (! in_array($protocolValue, ['http', 'socks5'], true)) {
            return;
        }
        $order->loadMissing(['activeDetail', 'product']);
        if (! $order->activeDetail || ! $order->product) {
            return;
        }

        $services = collect($order->activeDetail->additional_services ?? [])->filter(function ($s) {
            return ($s['service_type'] ?? '') !== 'protocol_select';
        });
        $row = getAdditionalServices($order->product, 'protocol_secimi', $protocolValue);
        if (($row['value'] ?? '') === '') {
            return;
        }
        $services->push($row);
        $order->activeDetail->update([
            'additional_services' => $services->values()->all(),
        ]);
    }

    /**
     * IPv4 Localtonet: sunucu tarafında proxy üzerinden ipify / Cloudflare erişim testi (local ve canlı).
     */
    public function v4ConnectivityTest(Request $request, Order $order)
    {
        $access = $this->v4EnsureOrderAccess($order);
        if ($access instanceof JsonResponse) {
            return $access;
        }

        if (! $order->isCanDeliveryType('LOCALTONETV4')) {
            return $this->errorResponse('Bu işlem yalnızca IPv4 Localtonet siparişleri için geçerlidir.');
        }
        if (! $order->isLocaltonetPortalOperationsAllowed()) {
            return $this->errorResponse('Sipariş henüz teslim edilmedi.');
        }

        $action = $request->input('action', 'proxy');
        if (! in_array($action, ['proxy', 'ping'], true)) {
            return $this->errorResponse('Geçersiz işlem.');
        }

        $inputTid = $request->input('tunnel_id');
        $tunnelId = ($inputTid !== null && $inputTid !== '') ? (int) $inputTid : null;

        $proxy = $this->getProxy($order, $tunnelId);
        if ($proxy instanceof JsonResponse) {
            return $proxy;
        }
        if (! is_array($proxy) || (! empty($proxy['hasError']))) {
            return $this->errorResponse($proxy['errorMessage'] ?? 'Proxy bilgisi alınamadı.');
        }

        $proxyUrl = $this->buildLocaltonetGuzzleProxyUrl($proxy);
        if ($proxyUrl === null) {
            return $this->errorResponse('Proxy adresi eksik.');
        }

        $targetUrl = $action === 'ping'
            ? 'https://1.1.1.1/cdn-cgi/trace'
            : 'https://api.ipify.org';

        $verify = config('services.localtonet.http_verify', true);
        $options = ['proxy' => $proxyUrl];
        if (is_bool($verify)) {
            $options['verify'] = $verify;
        } elseif (is_string($verify) && $verify !== '' && is_readable($verify)) {
            $options['verify'] = $verify;
        } else {
            $options['verify'] = true;
        }

        $t0 = microtime(true);
        try {
            $response = Http::withOptions($options)
                ->timeout(22)
                ->connectTimeout(14)
                ->get($targetUrl);
        } catch (\Throwable $e) {
            return $this->successResponse(null, [
                'ok' => false,
                'line' => 'Bağlantı hatası: '.$e->getMessage(),
                'ms' => null,
            ]);
        }

        $ms = (int) round((microtime(true) - $t0) * 1000);

        if (! $response->successful()) {
            return $this->successResponse(null, [
                'ok' => false,
                'line' => 'HTTP '.$response->status().' — tünel üzerinden hedefe ulaşılamadı.',
                'ms' => $ms,
            ]);
        }

        $body = trim($response->body());
        if ($action === 'proxy') {
            $line = 'Çıkış IP (ipify): '.($body !== '' ? $body : '(boş yanıt)');
        } else {
            $snippet = substr(preg_replace('/\s+/', ' ', $body), 0, 220);
            $line = 'Bağlantı / gecikme testi: OK (~'.$ms.' ms). Örnek yanıt: '.$snippet;
        }

        return $this->successResponse(null, [
            'ok' => true,
            'line' => $line,
            'ms' => $ms,
        ]);
    }

    private function v4EnsureOrderAccess(Order $order): JsonResponse|null
    {
        if (auth('admin')->check()) {
            return null;
        }
        if ($order->user_id !== auth()->id()) {
            return $this->errorResponse(__('error_response'));
        }

        return null;
    }

    private function buildLocaltonetGuzzleProxyUrl(array $proxy): ?string
    {
        $ip = trim((string) ($proxy['serverIp'] ?? ''));
        $port = isset($proxy['serverPort']) ? (string) $proxy['serverPort'] : '';
        if ($ip === '' || $port === '') {
            return null;
        }

        $auth = $proxy['authentication'] ?? [];
        $isActive = filter_var($auth['isActive'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $user = trim((string) ($auth['userName'] ?? ''));
        $pass = (string) ($auth['password'] ?? '');

        $scheme = (function_exists('localtonet_tunnel_result_is_socks') && localtonet_tunnel_result_is_socks($proxy))
            ? 'socks5'
            : 'http';

        $url = $scheme.'://';
        if ($isActive && $user !== '' && $pass !== '') {
            $url .= rawurlencode($user).':'.rawurlencode($pass).'@';
        }
        $url .= $ip.':'.$port;

        return $url;
    }

    public function lrChangePassword(Order $order, Request $request): JsonResponse
    {
        if ($order->user_id !== Auth::id()) {
            return $this->errorResponse('Yetkisiz işlem.');
        }
        if (! $order->isCanDeliveryType('LOCALTONET_ROTATING')) {
            return $this->errorResponse('Bu sipariş Localtonet Rotating değil.');
        }

        $pi = $order->product_info ?? [];
        $clients = $pi['lr_clients'] ?? [];
        $poolId = (int) ($pi['lr_pool_id'] ?? 0);

        $pool = \App\Models\LocaltonetRotatingPool::find($poolId);
        if (! $pool || ! $pool->api_key || count($clients) === 0) {
            return $this->errorResponse('Havuz veya istemci bilgisi bulunamadı.');
        }

        $newPassword = Str::random(12);
        $service = new \App\Services\LocaltonetService(null, $pool->api_key);

        foreach ($clients as $cc) {
            $tunnelId = (int) ($cc['tunnel_id'] ?? 0);
            $clientId = $cc['client_id'] ?? '';
            if ($tunnelId <= 0 || $clientId === '') continue;

            $res = $service->updateSharedProxyClient($tunnelId, $clientId, [
                'password' => $newPassword,
            ]);

            if (! empty($res['hasError'])) {
                Logger::error('LR_CHANGE_PASSWORD_ERROR', [
                    'order_id' => $order->id,
                    'tunnel_id' => $tunnelId,
                    'errors' => $res['errors'] ?? [],
                ]);
                return $this->errorResponse('Şifre değiştirme sırasında hata oluştu.');
            }
        }

        $pi['lr_password'] = $newPassword;
        $order->update(['product_info' => $pi]);

        return $this->successResponse('Şifre başarıyla değiştirildi.', ['new_password' => $newPassword]);
    }

    public function lrGetClients(Order $order): JsonResponse
    {
        if ($order->user_id !== Auth::id()) {
            return $this->errorResponse('Yetkisiz işlem.');
        }
        if (! $order->isCanDeliveryType('LOCALTONET_ROTATING')) {
            return $this->errorResponse('Bu sipariş Localtonet Rotating değil.');
        }

        $pi = $order->product_info ?? [];
        $clients = $pi['lr_clients'] ?? [];
        $poolId = (int) ($pi['lr_pool_id'] ?? 0);

        $pool = \App\Models\LocaltonetRotatingPool::find($poolId);
        if (! $pool || ! $pool->api_key || count($clients) === 0) {
            return $this->errorResponse('Bilgi bulunamadı.');
        }

        $service = new \App\Services\LocaltonetService(null, $pool->api_key);
        $tunnelId = (int) ($clients[0]['tunnel_id'] ?? 0);
        $clientId = $clients[0]['client_id'] ?? '';

        $listRes = $service->listSharedProxyClients($tunnelId);
        $allClients = $listRes['result'] ?? [];

        \App\Library\Logger::info('LR_GET_CLIENTS_DEBUG', [
            'order_id' => $order->id,
            'tunnel_id' => $tunnelId,
            'client_id' => $clientId,
            'api_response_keys' => is_array($allClients) && count($allClients) > 0 ? array_keys($allClients[0] ?? []) : 'empty',
            'first_client' => is_array($allClients) && count($allClients) > 0 ? $allClients[0] : null,
        ]);

        $myClient = null;
        if (is_array($allClients)) {
            foreach ($allClients as $c) {
                $cid = $c['id'] ?? $c['Id'] ?? null;
                if ($cid === $clientId) {
                    $myClient = $c;
                    break;
                }
            }
        }

        $bandwidthBytes = null;
        $usageBytes = null;
        $remainingBytes = null;
        $expiration = null;
        $threadLimit = null;

        if ($myClient) {
            $bwRaw = $myClient['bandwidthLimit'] ?? $myClient['BandwidthLimit'] ?? null;
            $usageRaw = $myClient['bandwidthUsage'] ?? $myClient['BandwidthUsage'] ?? 0;
            $dstRaw = $myClient['dataSizeType'] ?? $myClient['DataSizeType'] ?? null;

            $bwVal = is_numeric($bwRaw) ? (float) $bwRaw : 0;
            $usageVal = is_numeric($usageRaw) ? (float) $usageRaw : 0;

            if ($dstRaw !== null && is_numeric($dstRaw) && (int) $dstRaw > 1) {
                $multiplier = (float) $dstRaw;
                $bandwidthBytes = $bwVal * $multiplier;
                $usageBytes = $usageVal * $multiplier;
            } elseif ($bwVal > 0 && $bwVal < 1000) {
                $di = $order->product_data['delivery_items'] ?? [];
                $formSizeType = (int) ($di['quota']['data_size_type'] ?? 4);
                $multiplier = match ($formSizeType) {
                    1 => 1,
                    2 => 1024,
                    3 => 1048576,
                    4 => 1073741824,
                    5 => 1099511627776,
                    default => 1073741824,
                };
                $bandwidthBytes = $bwVal * $multiplier;
                $usageBytes = $usageVal * $multiplier;
            } else {
                $bandwidthBytes = $bwVal;
                $usageBytes = $usageVal;
            }

            if ($bandwidthBytes !== null && $bandwidthBytes > 0) {
                $remainingBytes = max(0, $bandwidthBytes - $usageBytes);
            }

            $expiration = $myClient['expirationDate'] ?? $myClient['ExpirationDate'] ?? null;
            $threadLimit = $myClient['threadLimit'] ?? $myClient['ThreadLimit'] ?? null;
        } else {
            $di = $order->product_data['delivery_items'] ?? [];
            $quotaSize = $di['quota']['data_size'] ?? null;
            $formSizeType = (int) ($di['quota']['data_size_type'] ?? 4);
            if ($quotaSize !== null && (float) $quotaSize > 0) {
                $multiplier = match ($formSizeType) {
                    1 => 1,
                    2 => 1024,
                    3 => 1048576,
                    4 => 1073741824,
                    5 => 1099511627776,
                    default => 1073741824,
                };
                $bandwidthBytes = (float) $quotaSize * $multiplier;
                $usageBytes = 0;
                $remainingBytes = $bandwidthBytes;
            }
        }

        return $this->successResponse('', [
            'lr_bandwidth' => $bandwidthBytes,
            'lr_usage' => $usageBytes,
            'lr_remaining' => $remainingBytes,
            'lr_expiration' => $expiration,
            'lr_thread_limit' => $threadLimit,
        ]);
    }

    public function threeProxyChangeCredentials(Order $order, Request $request)
    {
        if ((int) $order->user_id !== (int) Auth::id()) {
            return $this->errorResponse('Yetkisiz işlem.');
        }

        if (!$order->isThreeProxyDelivery() || $order->status !== 'ACTIVE') {
            return $this->errorResponse('Bu işlem yapılamaz.');
        }

        $request->validate([
            'username' => 'required|string|min:3|max:32',
            'password' => 'required|string|min:4|max:64',
        ]);

        $result = $order->threeProxyChangeCredentials($request->username, $request->password);

        if (!empty($result['success'])) {
            return $this->successResponse($result['message'] ?? 'Kullanıcı/şifre güncellendi.');
        }

        return $this->errorResponse($result['message'] ?? 'Güncelleme başarısız.');
    }
}
