<?php

namespace App\Listeners;

use App\Library\Logger;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SetAuthenticationWhenCreateLocaltonetProxy
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        $order = $event->order;
        $proxyId = isset($event->tunnelId) && $event->tunnelId !== null ? (int) $event->tunnelId : $order->getLocaltonetProxyId();
        if (!$proxyId){
            Logger::error("LOCALTONET_SET_AUTHENTICATION_FOR_TUNNEL_ERROR", ["order_id" => $order->id, "tunnelId" => $proxyId, "error_message" => "ProxyId bulunamadı."]);
            return;
        }

        $service = $order->resolveLocaltonetService();
        $ips = array_values(array_filter($event->v4RestrictionIps ?? [], function ($ip) {
            return is_string($ip) && filter_var(trim($ip), FILTER_VALIDATE_IP);

        }));

        if ($order->isCanDeliveryType('LOCALTONETV4') && count($ips) > 0) {
            $setAuthentication = $service->setAuthenticationForTunnel($proxyId, 0);
            if (@$setAuthentication['hasError']) {
                Logger::error('LOCALTONET_SET_AUTHENTICATION_FOR_TUNNEL_ERROR', ['order_id' => $order->id, 'tunnelId' => $proxyId, 'errorCode' => @$setAuthentication['errorCode'], 'errors' => @$setAuthentication['errors']]);
            }
            $del = $service->deleteAllIpRestrictions($proxyId);
            if (@$del['hasError']) {
                Logger::error('LOCALTONET_V4_DELETE_IP_RESTRICTIONS_ERROR', ['order_id' => $order->id, 'tunnelId' => $proxyId, 'errors' => @$del['errors']]);
            }
            foreach ($ips as $ip) {
                $add = $service->addIpRestriction($proxyId, trim($ip));
                if (@$add['hasError']) {
                    Logger::error('LOCALTONET_V4_ADD_IP_RESTRICTION_ERROR', ['order_id' => $order->id, 'tunnelId' => $proxyId, 'errors' => @$add['errors']]);
                }
            }
            $allow = $service->updateIsAllowForIpRestriction($proxyId, true);
            if (@$allow['hasError']) {
                Logger::error('LOCALTONET_V4_UPDATE_IP_ALLOW_ERROR', ['order_id' => $order->id, 'tunnelId' => $proxyId, 'errors' => @$allow['errors']]);
            }

            return;
        }

        $presetUser = isset($event->presetAuthUsername) ? trim((string) $event->presetAuthUsername) : '';
        $presetPass = isset($event->presetAuthPassword) ? (string) $event->presetAuthPassword : '';
        if ($presetUser !== '' && $presetPass !== '') {
            $setAuthentication = $service->setAuthenticationForTunnel($proxyId, 1, $presetUser, $presetPass);
        } else {
            $setAuthentication = $service->setAuthenticationForTunnel($proxyId, 1);
        }
        if (@$setAuthentication["hasError"]) {
            Logger::error("LOCALTONET_SET_AUTHENTICATION_FOR_TUNNEL_ERROR", ["order_id" => $order->id, "tunnelId" => $proxyId, "errorCode" => @$setAuthentication["errorCode"], "errors" => @$setAuthentication["errors"]]);
        }
    }
}
