<?php

namespace App\Http\Controllers\Admin;

use App\Events\CheckoutConfirmed;
use App\Http\Controllers\Controller;
use App\Models\Checkout;
use App\Models\Feedback;
use App\Models\SubscriptionProgress;
use App\Models\User;
use App\Notifications\CheckoutConfirmedNotification;
use App\Notifications\InvoiceCheckoutConfirmedNotification;
use App\Traits\AjaxResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use function Laravel\Prompts\error;

class CheckoutController extends Controller
{

    use AjaxResponses;

    public function index()
    {
        return view("admin.pages.checkouts.index");
    }

    public function store(Request $request, User $user)
    {
        DB::beginTransaction();
        try {

            DB::commit();
            return $this->successResponse("Ödeme başarıyla kaydedildi.");
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage());
        }
    }

    public function ajax(Request $request)
    {
        $whereSearch = "checkouts.deleted_at IS NULL ";
        $showAllList = $request->showAllList;
        if ($showAllList) {
            $searchableColumns = [
                "checkouts.id",
                db_user_full_name_expr('users'),
                "checkouts.type",
                "checkouts.status",
                "checkouts.created_at",
                "checkouts.amount"
            ];
        } else {
            $searchableColumns = [
                "checkouts.id",
                "checkouts.type",
                "checkouts.status",
                "checkouts.created_at",
                "checkouts.amount"
            ];

            $userId = $request->userId;
            if ($userId) {
                $whereSearch .= " AND checkouts.user_id = {$userId} ";
            }
        }

        if (isset($request->order[0]["column"]) and isset($request->order[0]["dir"])) {
            $orderBy = $searchableColumns[$request->order[0]["column"]] . " " . $request->order[0]["dir"];
        } else {
            $orderBy = "checkouts.id DESC";
        }

        $searchVal = $request->search["value"];
        if ($searchVal) {
            $whereSearch .= " AND (";
            foreach ($searchableColumns as $key => $searchableColumn) {
                $whereSearch .= "$searchableColumn LIKE '%{$searchVal}%'";
                if (array_key_last($searchableColumns) != $key) {
                    $whereSearch .= " OR ";
                } else {
                    $whereSearch .= ")";
                }
            }
        }

        $status = $request->status;
        if ($status) {
            $whereSearch .= " AND checkouts.status = '{$status}' ";
        }

        $start = $request->start ?? 0;
        $length = $request->length == -1 ? 10 : $request->length;

        $query = Checkout::select(
            'checkouts.*',
            'users.first_name as user_first_name',
            'users.last_name as user_last_name',
            DB::raw(db_user_full_name_expr('users').' as user_name'),
        )
            ->leftJoin('users', 'users.id', '=', 'checkouts.user_id')
            ->whereRaw($whereSearch)
            ->orderByRaw($orderBy)
            ->skip($start)->take($length);

        $list = $query->get();

        $countTotalRecords = $query->count();

        $query = Checkout::select(
            'checkouts.*',
            DB::raw(db_user_full_name_expr('users').' as user_name')
        )
            ->leftJoin('users', 'users.id', '=', 'checkouts.user_id')
            ->whereRaw($whereSearch)
            ->orderByRaw($orderBy);

        $countFilteredRecords = $query->count();
        $data = [];

        foreach ($list as $item) {
            $bg = "";
            switch ($item->status) {
                case "NEW":
                    $bg = "light-primary";
                    break;
                case "WAITING_APPROVAL":
                    $bg = "light-warning";
                    break;
                case "3DS_REDIRECTED":
                    $bg = "light-info";
                    break;
                case "COMPLETED":
                    $bg = "light-success";
                    break;
                case "FAILED":
                    $bg = "light-danger";
                    break;
                case "CANCELLED":
                    $bg = "secondary";
                    break;
            }
            $typeConfig = [
                'TRANSFER' => ['icon' => 'fa-building-columns', 'class' => 'badge-light-info', 'label' => 'Havale/EFT'],
                'CREDIT_CARD' => ['icon' => 'fa-credit-card', 'class' => 'badge-light-primary', 'label' => 'Kredi Kartı'],
                'BALANCE' => ['icon' => 'fa-wallet', 'class' => 'badge-light-success', 'label' => 'Bakiye'],
            ];
            $tc = $typeConfig[$item->type] ?? ['icon' => 'fa-money-bill', 'class' => 'badge-secondary', 'label' => $item->type];
            $typeBadge = "<span class='badge {$tc['class']} fs-7'><i class='fa {$tc['icon']} me-1'></i>{$tc['label']}</span>";

            $statusConfig = [
                'NEW' => ['class' => 'badge-light-primary', 'label' => 'Yeni'],
                'WAITING_APPROVAL' => ['class' => 'badge-light-warning', 'label' => 'Bekliyor'],
                '3DS_REDIRECTED' => ['class' => 'badge-light-info', 'label' => '3D Secure'],
                'COMPLETED' => ['class' => 'badge-light-success', 'label' => 'Tamamlandı'],
                'FAILED' => ['class' => 'badge-light-danger', 'label' => 'Başarısız'],
                'CANCELLED' => ['class' => 'badge-secondary', 'label' => 'İptal'],
            ];
            $sc = $statusConfig[$item->status] ?? ['class' => 'badge-secondary', 'label' => $item->status];
            $statusBadge = "<span class='badge {$sc['class']} fs-7'>{$sc['label']}</span>";

            if ($showAllList) {
                $data[] = [
                    "<span data-id='" . $item->id . "' data-bg='" . $bg . "' class='badge badge-sm badge-light-primary'>#" . $item->id . "</span>",
                    "<a target='_blank' href='" . route("admin.users.show", ["user" => $item->user_id]) . "'>" . $item->user_name . "</a>",
                    $typeBadge,
                    $statusBadge,
                    $item->created_at->format('d/m/Y H:i:s'),
                    $item->draw_amount
                ];
            } else {
                $data[] = [
                    "<span data-id='" . $item->id . "' data-bg='" . $bg . "' class='badge badge-sm badge-light-primary'>#" . $item->id . "</span>",
                    $typeBadge,
                    $statusBadge,
                    $item->created_at->format('d/m/Y H:i:s'),
                    $item->draw_amount
                ];
            }
        }

        $response = array(
            'recordsTotal' => $countTotalRecords,
            'recordsFiltered' => $countFilteredRecords,
            'data' => $data
        );
        echo json_encode($response);
    }

    public function find(Checkout $checkout)
    {
        $checkout->load(["user", "invoice"]);
        $checkout->user_detail_url = route("admin.users.show", ["user" => $checkout->user->id]);
        $checkout->invoice_detail_url = $checkout->invoice ? route("admin.invoices.show", ["invoice" => $checkout->invoice->id]) : null;

        return $this->successResponse("", ["data" => $checkout]);
    }

    public function paymentStatusUpdate(Request $request, Checkout $checkout)
    {
        $type = $request->type;
        if ($type != "COMPLETED" && $type != "CANCELLED") return $this->errorResponse("Eksik parametre.");

        $checkout->status = $type;
        if ($checkout->save()) {
            if ($type == 'COMPLETED') {
                event(new CheckoutConfirmed($checkout));
                $deferred = config('queue.checkout_deferred_connection', 'database');
                $notification = new InvoiceCheckoutConfirmedNotification($checkout->invoice);
                $notification->onConnection($deferred);
                $checkout->user->notify($notification);
                \App\Services\NotificationTemplateService::send('invoice_paid', $checkout->user, [
                    'fatura_no' => $checkout->invoice->invoice_number ?? $checkout->invoice->id,
                    'tutar' => number_format($checkout->invoice->total_price_with_vat ?? 0, 2, ',', '.'),
                    'fatura_url' => url('/invoices/' . $checkout->invoice->id),
                ]);
            }
            return $this->successResponse("Ödeme bildirimi başarıyla kaydedildi.");
        }
        return $this->errorResponse("Bir sorun oluştu");
    }
}
