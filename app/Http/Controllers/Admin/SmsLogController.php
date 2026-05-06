<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SmsLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SmsLogController extends Controller
{
    public function index()
    {
        return view("admin.pages.smsLogs.index");
    }
    public function ajax(Request $request)
    {
        $whereSearch = "sms_logs.deleted_at IS NULL ";
        $showAllList = $request->showAllList;
        if ($showAllList) {
            $searchableColumns = [
                "sms_logs.id",
                db_user_full_name_expr('users'),
                "sms_logs.body",
                "sms_logs.number",
                "sms_logs.created_at",
                "sms_logs.status"
            ];
        } else {
            $searchableColumns = [
                "sms_logs.id",
                "sms_logs.body",
                "sms_logs.number",
                "sms_logs.created_at",
                "sms_logs.status"
            ];

            $userId = $request->userId;
            if ($userId) {
                $whereSearch .= " AND sms_logs.user_id = {$userId} ";
            }
        }

        if (isset($request->order[0]["column"]) and isset($request->order[0]["dir"])) {
            $orderBy = $searchableColumns[$request->order[0]["column"]] . " " . $request->order[0]["dir"];
        } else {
            $orderBy = "sms_logs.id DESC";
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
            $whereSearch .= " AND sms_logs.status = '{$status}' ";
        }

        $start = $request->start ?? 0;
        $length = $request->length == -1 ? 10 : $request->length;

        $query = SmsLog::select(
            'sms_logs.*',
            'users.first_name as user_first_name',
            'users.last_name as user_last_name',
            DB::raw(db_user_full_name_expr('users').' as user_name'),
        )
            ->leftJoin('users', 'users.id', '=', 'sms_logs.user_id')
            ->whereRaw($whereSearch)
            ->orderByRaw($orderBy)
            ->skip($start)->take($length);

        $list = $query->get();

        $countTotalRecords = $query->count();

        $query = SmsLog::select(
            'sms_logs.*',
            DB::raw(db_user_full_name_expr('users').' as user_name')
        )
            ->leftJoin('users', 'users.id', '=', 'sms_logs.user_id')
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
                    "<a target='_blank' href='" . route("admin.users.show", ["user" => $item->user_id]) . "'>" . $item->user_name . "</a>",
                    $item->body,
                    $item->number,
                    $date,
                    $status
                ];
            } else {
                $data[] = [
                    "<span data-id='" . $item->id . "' class='badge badge-sm badge-light-primary'>#" . $item->id . "</span>",
                    $item->body,
                    $item->number,
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

}
