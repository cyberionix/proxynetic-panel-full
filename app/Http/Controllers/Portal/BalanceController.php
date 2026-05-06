<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\BalanceActivity;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Traits\AjaxResponses;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BalanceController extends Controller
{
    use AjaxResponses;
    public function ajax(Request $request)
    {
        $whereSearch = "balance_activities.deleted_at IS NULL";
        $showAllList = $request->showAllList;

        $searchableColumns = [
            "balance_activities.id",
            "balance_activities.amount",
            "balance_activities.created_at",
        ];
        $userId = Auth::id();
        if ($userId) {
            $whereSearch .= " AND balance_activities.user_id = {$userId} ";
        }

        if (isset($request->order[0]["column"]) and isset($request->order[0]["dir"])) {
            $orderBy = $searchableColumns[$request->order[0]["column"]] . " " . $request->order[0]["dir"];
        } else {
            $orderBy = "balance_activities.id DESC";
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

//        $status = $request->status;
//        if ($status) {
//            $whereSearch .= " AND balance_activities.status = '{$status}' ";
//        }

        $start = $request->start ?? 0;
        $length = $request->length == -1 ? 10 : $request->length;

        $query = BalanceActivity::select(
            'balance_activities.*'
        )
            ->whereRaw($whereSearch)
            ->orderByRaw($orderBy);
        $countFilteredRecords = $query->count();
        $query = $query->skip($start)->take($length);

        $list = $query->get();
        $countTotalRecords = $query->count();

        $data = [];
        foreach ($list as $item) {
            $bg = $item->type == "IN" ? "light-success" : "light-danger";
            $data[] = [
                "<span data-id='" . $item->id . "' data-bg='" . $bg . "' class='badge badge-sm badge-light-primary'>#" . $item->id . "</span>",
                showBalance($item->amount, true),
                $item->created_at->format(defaultDateTimeFormat()),
                $item->drawPortalAction()
            ];
        }

        $response = array(
            'recordsTotal' => $countTotalRecords,
            'recordsFiltered' => $countFilteredRecords,
            'data' => $data
        );
        echo json_encode($response);
    }
    public function index()
    {
        return view("portal.pages.balance.index");
    }
    public function addBalancePost(Request $request)
    {
        $request->validate([
            "balance" => "required",
        ], [
            'balance.required' => __('custom_field_is_required', ['name' => __('balance')]),
        ]);

        $balance = commaToDot($request->balance);
        if ($balance < 100) return $this->errorResponse(__("you_can_load_a_loan_over_a_minimum_amount_of_100"));

        DB::beginTransaction();
        try {
            $invoice = Invoice::create([
                "invoice_number" => Invoice::generateInvoiceNumber(),
                "invoice_date" => Carbon::now(),
                "due_date" => Carbon::now()->addDay(),
                "status" => "PENDING",
                "total_price" => $balance,
                "total_vat" => 0,
                "total_price_with_vat" => $balance,
                "user_id" => Auth::id()
            ]);

            InvoiceItem::create([
                "type" => "BALANCE",
                "name" => __("account_credit"),
                "total_price" => $balance,
                "vat_percent" => 0,
                "total_price_with_vat" => $balance,
                "invoice_id" => $invoice->id,
            ]);

            DB::commit();
            return $this->successResponse(__("created_response", ["name" => __("invoice")]), ["redirectUrl" => route("portal.invoices.show", ["invoice" => $invoice->id])]);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse(__("error_response"), ["error" => $e->getMessage()]);
        }
    }
}
