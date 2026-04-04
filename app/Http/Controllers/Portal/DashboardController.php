<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Plan;
use App\Models\SubscriptionPlan;
use App\Models\Support;
use App\Models\User;
use App\Traits\AjaxResponses;
use Dflydev\DotAccessData\Data;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    use AjaxResponses;
    public function index()
    {

        $orders = Order::with('product')->whereUserId(Auth::id())->orderByDesc("id")->limit(5)->get();
        $invoices = Invoice::whereUserId(Auth::id())->orderByDesc("id")->limit(5)->get();
        return view('portal.pages.dashboard.index', compact("orders", "invoices"));
    }

    public function getData()
    {
        $orders = Order::whereUserId(Auth::id())->whereStatus("ACTIVE")->orderByDesc("id")->limit(4)->get();
        foreach ($orders as $order){
            $order->drawEndDate = $order->end_date?->format(defaultDateFormat());
            $order->viewRoute = route("portal.orders.show", ["order" => $order->id]);
        }

        $supports = Support::whereUserId(Auth::id())->orderByDesc("id")->limit(4)->get();
        foreach ($supports as $support){
            $support->drawStatusBadge = $support->drawStatusBadge();
            $support->drawAction = "<a href='" . route("portal.supports.show", ["support" => $support->id]) . "' class='btn btn-light-primary btn-sm'>" . __("view") . "</a>";
        }

        return $this->successResponse("", [
            "info_card" => [
                "order" => Auth::user()->orders->count(),
                "support" => Support::whereUserId(Auth::id())->get()->count(),
                "invoice" => Invoice::whereUserId(Auth::id())->get()->count(),
            ],
            "orders" => $orders,
            "supports" => $supports
        ]);
    }
}
