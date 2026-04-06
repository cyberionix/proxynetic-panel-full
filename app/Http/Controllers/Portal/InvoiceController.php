<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index()
    {
        return view("portal.pages.invoices.index");
    }

    public function ajax(Request $request, User $user)
    {
        $searchableColumns = [
            "invoice_number",
            "invoice_date",
            "total_price_with_vat",
            "status"
        ];

        $whereSearch = "deleted_at IS NULL AND user_id = {$user->id} ";

        $statusFilter = $request->input('status_filter', 'all');
        if ($statusFilter && $statusFilter !== 'all') {
            $whereSearch .= " AND status = '" . addslashes($statusFilter) . "' ";
        }

        if (isset($request->order[0]["column"]) and isset($request->order[0]["dir"])) {
            $orderBy = $searchableColumns[$request->order[0]["column"]] . " " . $request->order[0]["dir"];
        } else {
            $orderBy = "id DESC";
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

        $start = $request->start ?? 0;
        $length = $request->length == -1 ? 10 : $request->length;

        $query = Invoice::whereRaw($whereSearch)
            ->orderByRaw($orderBy)
            ->skip($start)->take($length);

        $list = $query->get();

        $countTotalRecords = $query->count();

        $query = Invoice::whereRaw($whereSearch)
            ->orderByRaw($orderBy);

        $countFilteredRecords = $query->count();
        $data = [];

        foreach ($list as $item) {
            $data[] = [
                "<span data-id='" . $item->id . "' class='badge badge-sm badge-light-primary'>#" . $item->invoice_number . "</span>",
                "<span class='badge badge-secondary'>" . convertDate($item->invoice_date) . "</span>",
                "<span class='badge badge-secondary badge-lg'>" . showBalance($item->total_price_with_vat, true) . "</span>",
                $item->drawStatus(),
                "<a href='" . route("portal.invoices.show", ["invoice" => $item->id]) . "' class='btn btn-light-primary btn-sm'>" . __("view") . "</a>"
            ];
        }

        $response = array(
            'recordsTotal' => $countTotalRecords,
            'recordsFiltered' => $countFilteredRecords,
            'data' => $data
        );
        echo json_encode($response);
    }

    public function show(Invoice $invoice)
    {
        if ($invoice->user_id != Auth::id()) return redirect()->route("portal.invoices.index");
        $invoice->load("items");

        return view("portal.pages.invoices.details.index", compact("invoice"));
    }
}
