<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\UserSession;
use App\Traits\AjaxResponses;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserSessionController extends Controller
{
    use AjaxResponses;

    public function index()
    {
        return view("admin.pages.userSessions.index");
    }

    public function ajax(Request $request)
    {
        $showAllList = $request->showAllList;
        $whereSearch = "user_sessions.deleted_at IS NULL ";
        if ($showAllList) {
            $searchableColumns = [
                "user_sessions.id",
                db_user_full_name_expr('users'),
                "user_sessions.ip_address",
                "user_sessions.login_date",
            ];
        } else {
            $searchableColumns = [
                "user_sessions.id",
                "user_sessions.ip_address",
                "user_sessions.login_date",
            ];
            $userId = $request->userId;
            if ($userId) {
                $whereSearch .= " AND user_sessions.user_id = {$userId} ";
            }
        }

        if (isset($request->order[0]["column"]) and isset($request->order[0]["dir"])) {
            $orderBy = $searchableColumns[$request->order[0]["column"]] . " " . $request->order[0]["dir"];
        } else {
            $orderBy = "user_sessions.id DESC";
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

        $query = UserSession::select(
            'user_sessions.id as id',
            'user_sessions.user_id as user_id',
            'user_sessions.ip_address as ip_address',
            'user_sessions.login_date as login_date',
            DB::raw(db_user_full_name_expr('users').' as user_name'),
        )
            ->leftJoin('users', 'users.id', '=', 'user_sessions.user_id')
            ->whereRaw($whereSearch)
            ->orderByRaw($orderBy);

        $countFilteredRecords = $query->count();
        $query = $query->skip($start)->take($length);

        $list = $query->get();
        $countTotalRecords = $query->count();

        $data = [];

        foreach ($list as $item) {
            if ($showAllList) {
                $data[] = [
                    $item->id,
                    "<a target='_blank' href='" . route("admin.users.show", ["user" => $item->user_id]) . "'>" . $item->user_name . "</a>",
                    "<span class='badge badge-secondary badge-lg'>" . $item->ip_address . "</span>",
                    $item->login_date->format(defaultDateTimeFormat())
                ];
            } else {
                $data[] = [
                    $item->id,
                    "<span class='badge badge-secondary badge-lg'>" . $item->ip_address . "</span>",
                    $item->login_date->format(defaultDateTimeFormat())
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
