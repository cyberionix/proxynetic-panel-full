<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Checkout;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Student;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionProgress;
use App\Models\Support;
use App\Models\User;
use App\Traits\AjaxResponses;
use Carbon\Carbon;
use Dflydev\DotAccessData\Data;
use Illuminate\Http\Request;
use Spatie\GoogleCalendar\Event;

class DashboardController extends Controller
{
    use AjaxResponses;

    public function index()
    {
        return view("admin.pages.dashboard.index");
    }
    public function getSaleReports()
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->startOfMonth()->endOfMonth();

        $startOfLastMonth = Carbon::now()->startOfMonth()->subMonth();
        $endOfLastMonth = Carbon::now()->startOfMonth()->subMonth()->endOfMonth();

        $today = Checkout::where('paid_at', '>=', today()->format('Y-m-d').' 00:00:00')
            ->where('paid_at', '<=', today()->format('Y-m-d').' 23:59:59')->whereStatus("COMPLETED");

        $thisMonth = Checkout::whereStatus("COMPLETED")->whereNotNull("paid_at")->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
        $lastMonth = Checkout::whereStatus("COMPLETED")->whereNotNull("paid_at")->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth]);

        $daysInMonth = Carbon::now()->daysInMonth;
        $dailyAvg = ($thisMonth->sum("amount") ?? 0) / $daysInMonth;

        $data = [
            "today_price" => showBalance($today->sum("amount") ?? 0),
            "today_count" => $today->count(),
            "draw_daily_avg" => showBalance($dailyAvg, true),
            "draw_this_month_amount" => showBalance($thisMonth->sum("amount") ?? 0, true),
            "draw_this_month_count" => "(" . $thisMonth->count() . ")",
            "draw_last_month_amount" => showBalance($lastMonth->sum("amount") ?? 0, true),
            "draw_last_month_count" => "(" . $lastMonth->count() . ")"
        ];

        return $this->successResponse("", ["data" => $data]);
    }
    public function getCustomerReports()
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->startOfMonth()->endOfMonth();

        $startOfLastMonth = Carbon::now()->startOfMonth()->subMonth();
        $endOfLastMonth = Carbon::now()->startOfMonth()->subMonth()->endOfMonth();

        $data = [
            "today" => User::whereIsBanned(0)->whereDay("created_at", Carbon::today())->count() ?? 0,
            "total" => User::whereIsBanned(0)->count() ?? 0,
            "this_month" => User::whereIsBanned(0)->whereBetween('created_at', [$startOfMonth, $endOfMonth])->count() ?? 0,
            "last_month" => User::whereIsBanned(0)->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count() ?? 0,
        ];

        return $this->successResponse("", ["data" => $data]);
    }
    public function getSupportReports()
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->startOfMonth()->endOfMonth();

        $data = [
            "total_pending" => Support::whereStatus("WAITING_FOR_AN_ANSWER")->whereIsLocked(0)->count() ?? 0,
            "today" => Support::whereStatus("ANSWERED")->whereIsLocked(0)->whereDay("created_at", Carbon::today())->count() ?? 0,
            "yesterday" => Support::whereStatus("ANSWERED")->whereIsLocked(0)->whereDate('created_at', Carbon::yesterday())->count() ?? 0,
            "this_month" => Support::whereStatus("ANSWERED")->whereIsLocked(0)->whereBetween('created_at', [$startOfMonth, $endOfMonth])->count() ?? 0,
        ];

        return $this->successResponse("", ["data" => $data]);
    }
    public function getLastOrders(Request $request)
    {
        $limit = is_numeric($request->input('limit')) ? (int)$request->input('limit') : 5;

        $orders = Order::with('user')->orderBy("id", "desc")->limit($limit)->get();

        $data = [];
        foreach ($orders as $order){
            $price_data = $order?->activeDetail?->price_data;
            $orderShowUrl = route('admin.orders.show', ['order' => $order->id]);
            $productTitle = e($order->product_data['name'] ?? '');
            $durationPart = trim((string) (@$price_data['duration'] ?? '').' '.convertDurationText(@$price_data['duration_unit'] ?? ''));
            $productBadge = '<a href="'.$orderShowUrl.'" class="text-gray-800 text-hover-primary text-decoration-none">'
                .'<span class="badge badge-light-primary">'.$productTitle.' / '.e($durationPart).'</span></a>';

            $data[] = [
                "id" => "<span class='badge badge-sm badge-light-primary'>#" . $order->id . "</span>",
                "user" => "<a href='" . route("admin.users.show", ["user" => $order->user_id]) . "'>" . $order?->user?->full_name . "</a>",
                "product_name" => $productBadge,
                "delivery_status" => $order->drawDeliveryStatus(),
                "amount" => "<span class='badge badge-secondary badge-lg'>" . showBalance(@$price_data["price_with_vat"], true) . "</span>",
                "redirect_url" => "<a href='" . route("admin.orders.show", ["order" => $order->id]) . "' class='btn btn-light-primary btn-sm'>" . __("view") . "</a>"
            ];
        }

        return $this->successResponse("", ["data" => $data]);
    }
    public function getPendingSupports(Request $request)
    {
        $limit = is_numeric($request->input('limit')) ? (int)$request->input('limit') : 5;

        $supports = Support::with('user')->where("status", "WAITING_FOR_AN_ANSWER")->orderBy("id", "desc")->limit($limit)->get();

        $data = [];
        foreach ($supports as $support){
            $data[] = [
                "id" => "<span class='badge badge-sm badge-light-primary'>#" . $support->id . "</span>",
                "user" => "<a href='" . route("admin.users.show", ["user" => $support->user_id]) . "'>" . $support->user->full_name . "</a>",
                "subject" => "<a href='" . route("admin.supports.show", ["support" => $support->id]) . "'>" . $support->subject . "</a>",
                "status" => $support->drawStatusBadge(),
                "redirect_url" => "<a href='" . route("admin.supports.show", ["support" => $support->id]) . "' class='btn btn-light-primary btn-sm'>" . __("view") . "</a>"
            ];
        }

        return $this->successResponse("", ["data" => $data]);
    }
    public function getUpcomingInvoices(Request $request)
    {
        $limit = is_numeric($request->input('limit')) ? (int)$request->input('limit') : 5;
        $invoices = Invoice::with('user')->whereDate('due_date', '>=', Carbon::now())->orderBy("due_date", "ASC")->orderBy("invoice_number", "DESC")->limit($limit)->get();

        $data = [];
        foreach ($invoices as $invoice){
            $data[] = [
                "id" => "<span class='badge badge-sm badge-light-primary'>#" . $invoice->invoice_number . "</span>",
                "user" => "<a href='" . route("admin.users.show", ["user" => $invoice->user_id]) . "'>" . $invoice->user?->full_name . "</a>",
                "due_date" => "<span class='badge badge-secondary'>" . $invoice->due_date->format(defaultDateFormat()) . "</span>",
                "amount" => "<span class='badge badge-secondary badge-lg'>" . showBalance($invoice->total_price_with_vat, true) . "</span>",
                "redirect_url" => "<a href='" . route("admin.invoices.show", ["invoice" => $invoice->id]) . "' class='btn btn-light-primary btn-sm'>" . __("view") . "</a>"
            ];
        }

        return $this->successResponse("", ["data" => $data]);
    }
}
