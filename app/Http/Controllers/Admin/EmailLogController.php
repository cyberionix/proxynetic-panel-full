<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class EmailLogController extends Controller
{
    public function index()
    {
        return view("admin.pages.emailLogs.index");
    }
    public function ajax(Request $request)
    {
        $whereSearch = "email_logs.deleted_at IS NULL ";
        $showAllList = $request->showAllList;
        if ($showAllList) {
            $searchableColumns = [
                "email_logs.id",
                db_user_full_name_expr('users'),
                "email_logs.body",
                "email_logs.receipt",
                "email_logs.created_at"
            ];
        } else {
            $searchableColumns = [
                "email_logs.id",
                "email_logs.body",
                "email_logs.receipt",
                "email_logs.created_at"
            ];

            $userId = $request->userId;
            if ($userId) {
                $whereSearch .= " AND email_logs.user_id = {$userId} ";
            }
        }

        if (isset($request->order[0]["column"]) and isset($request->order[0]["dir"])) {
            $orderBy = $searchableColumns[$request->order[0]["column"]] . " " . $request->order[0]["dir"];
        } else {
            $orderBy = "email_logs.id DESC";
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
            $whereSearch .= " AND email_logs.status = '{$status}' ";
        }

        $start = $request->start ?? 0;
        $length = $request->length == -1 ? 10 : $request->length;

        $query = EmailLog::select(
            'email_logs.*',
            'users.first_name as user_first_name',
            'users.last_name as user_last_name',
            DB::raw(db_user_full_name_expr('users').' as user_name'),
        )
            ->leftJoin('users', 'users.id', '=', 'email_logs.user_id')
            ->whereRaw($whereSearch)
            ->orderByRaw($orderBy)
            ->skip($start)->take($length);

        $list = $query->get();

        $countTotalRecords = $query->count();

        $query = EmailLog::select(
            'email_logs.*',
            DB::raw(db_user_full_name_expr('users').' as user_name')
        )
            ->leftJoin('users', 'users.id', '=', 'email_logs.user_id')
            ->whereRaw($whereSearch)
            ->orderByRaw($orderBy);

        $countFilteredRecords = $query->count();
        $data = [];

        foreach ($list as $item) {
            $status = "";
            switch ($item->status){
                case "SUCCESS":
                    $status = '<span class="badge badge-success badge-sm">' . $item->status . '</span>';
                    break;
                case "PENDING":
                    $status = '<span class="badge badge-warning badge-sm">' . $item->status . '</span>';
                    break;
                case "ERROR":
                    $status = '<span class="badge badge-danger badge-sm">' . $item->status . '</span>';
                    break;
            }
            $date = '<span class="badge badge-secondary badge-sm">' . $item->created_at->format(defaultDateTimeFormat()) . '</span>';
            if ($showAllList) {
                $data[] = [
                    "<span data-id='" . $item->id . "' class='badge badge-sm badge-light-primary'>#" . $item->id . "</span>",
                    $item?->user_name ? "<a target='_blank' href='" . route("admin.users.show", ["user" => $item->user_id]) . "'>" . $item->user_name . "</a>": '-',
                    $item->subject,
                    $item->receipt,
                    $date,
                    $status
                ];
            } else {
                $data[] = [
                    "<span data-id='" . $item->id . "' class='badge badge-sm badge-light-primary'>#" . $item->id . "</span>",
                    $item->subject,
                    $item->receipt,
                    $date,
                    $status
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

    public function find($id = null)
    {
        $email = EmailLog::findOrFail($id);
$email->body = base64_decode($email->body);
        return [
            'success' => true,
            'data' => $email
        ];
    }
}
