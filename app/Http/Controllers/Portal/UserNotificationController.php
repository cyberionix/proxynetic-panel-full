<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserNotificationResource;
use App\Models\User;
use App\Traits\AjaxResponses;
use Carbon\Carbon;
use Google\Service\Books\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserNotificationController extends Controller
{
    use AjaxResponses;

    public function list()
    {
        $data = [];
        $unreadCount = 0;

        $user = Auth::user();
        $notifications = $user->unreadNotifications->merge($user->readNotifications);
        foreach ($notifications as $notification) {
            $messageData = $notification["data"];
            $messageData["notification_id"] = $notification["id"];

            $data[] = [
                "created_at" => $notification["created_at"]->format(defaultDateTimeFormat()),
                "id" => $notification["id"],
                "type" => $notification["type"],
                "data" => $notification["data"],
                "message" => __("user_notifications." . $notification["type"], $messageData),
                "read_at" => $notification["read_at"],
                "time_ago" => $notification["created_at"] ? Carbon::diffText($notification["created_at"]) : ""
            ];
            if (!isset($notification["read_at"])) {
                $unreadCount++;
            }
        }
        return $this->successResponse("", ["unreadCount" => $unreadCount, "data" => $data]);
    }

    public function redirect(Request $request)
    {
        $routeName = $request->routeName ?? "";
        $notification = Auth::user()->notifications()->find($request->notificationId);
        $notification->markAsRead();

        return match ($routeName) {
            "portal.orders.show" => redirect()->route("portal.orders.show", ["order" => @$notification->data["order_id"]]),
            "portal.invoices.show" => redirect()->route("portal.invoices.show", ["invoice" => @$notification->data["invoice_id"]]),
            "portal.supports.show" => redirect()->route("portal.supports.show", ["support" => @$notification->data["support_id"]]),
            default => redirect()->route("portal.dashboard"),
        };
    }
}
