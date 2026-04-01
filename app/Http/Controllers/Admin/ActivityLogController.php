<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityLogController extends Controller
{
    public function index()
    {
        return view("admin.pages.activityLogs.index");
    }

    public function ajax(Request $request)
    {
        $whereSearch = "activity_logs.deleted_at IS NULL";
        $showAllList = $request->showAllList;

        if ($showAllList) {
            $searchableColumns = [
                "activity_logs.id",
                db_user_full_name_expr('users'),
                "activity_logs.id",
                "activity_logs.id",
                "activity_logs.created_at"
            ];
        } else {
            $searchableColumns = [
                "activity_logs.id",
                "activity_logs.id",
                "activity_logs.id",
                "activity_logs.created_at"
            ];
            $userId = $request->userId;
            if ($userId) {
                $whereSearch .= " AND activity_logs.user_id = {$userId} ";
            }
        }

        if (isset($request->order[0]["column"]) and isset($request->order[0]["dir"])) {
            $orderBy = $searchableColumns[$request->order[0]["column"]] . " " . $request->order[0]["dir"];
        } else {
            $orderBy = "activity_logs.id DESC";
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

        $query = ActivityLog::select(
            'activity_logs.*',
            DB::raw(db_user_full_name_expr('users').' as user_name'),
        )
            ->leftJoin('users', 'users.id', '=', 'activity_logs.user_id')
            ->whereRaw($whereSearch)
            ->orderByRaw($orderBy)
            ->skip($start)->take($length);

        $list = $query->get();

        $countTotalRecords = $query->count();

        $query = ActivityLog::select(
            'activity_logs.*',
            DB::raw(db_user_full_name_expr('users').' as user_name'),
        )
            ->leftJoin('users', 'users.id', '=', 'activity_logs.user_id')
            ->whereRaw($whereSearch)
            ->orderByRaw($orderBy);

        $countFilteredRecords = $query->count();
        $data = [];

        foreach ($list as $item) {
            $response = "";
            if (isset($item->data["response"])) {
                if (isset($item->data["response"]["success"])) {
                    $text = $item->data["response"]["success"]
                        ? __("activity_logs.response_success")
                        : __("activity_logs.response_error");
                    $class = $item->data["response"]["success"] ? "success" : "danger";
                    $response .= "<span class='badge badge-" . $class . " mb-2'>" . e($text) . "</span><br>";
                }
                if (isset($item->data["response"]["message"])) {
                    $response .= "<span class='fs-7'>" . $item->data["response"]["message"] . "</span>";
                }
            }
            if ($showAllList) {
                $data[] = [
                    "<span data-id='" . $item->id . "' class='badge badge-sm badge-light-primary'>#" . $item->id . "</span>",
                    "<a target='_blank' href='" . route("admin.users.show", ["user" => $item->user_id]) . "'>" . $item->user_name . "</a>",
                    e(activity_log_label($item->route)),
                    $response,
                    "<span class='badge badge-secondary'>" . $item->created_at->format(defaultDateTimeFormat()) . "</span>",
                ];
            } else {
                $data[] = [
                    "<span data-id='" . $item->id . "' class='badge badge-sm badge-light-primary'>#" . $item->id . "</span>",
                    e(activity_log_label($item->route)),
                    $response,
                    "<span class='badge badge-secondary'>" . $item->created_at->format(defaultDateTimeFormat()) . "</span>",
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
