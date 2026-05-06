<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BalanceActivity;
use App\Models\User;
use App\Traits\AjaxResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserBalanceController extends Controller
{
    use AjaxResponses;
    public function ajax(Request $request)
    {
        $whereSearch = "balance_activities.deleted_at IS NULL";
        $showAllList = $request->showAllList;

        if ($showAllList) {

        } else {
            $searchableColumns = [
                "balance_activities.id",
                "balance_activities.amount",
                "balance_activities.created_at",
            ];
            $userId = $request->userId;
            if ($userId) {
                $whereSearch .= " AND balance_activities.user_id = {$userId} ";
            }
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
            'balance_activities.*',
            DB::raw(db_user_full_name_expr('users').' as user_name'),
        )
            ->leftJoin('users', 'users.id', '=', 'balance_activities.user_id')
            ->whereRaw($whereSearch)
            ->orderByRaw($orderBy);
        $countFilteredRecords = $query->count();
        $query = $query->skip($start)->take($length);

        $list = $query->get();
        $countTotalRecords = $query->count();

        $data = [];
        foreach ($list as $item) {
            $bg = $item->type == "IN" ? "light-success" : "light-danger";
            if ($showAllList) {

            } else {
                $data[] = [
                    "<span data-id='" . $item->id . "' data-bg='" . $bg . "' class='badge badge-sm badge-light-primary'>#" . $item->id . "</span>",
                    showBalance($item->amount, true),
                    $item->created_at->format(defaultDateTimeFormat()),
                    $item->drawAdminAction()
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

    public function input(Request $request, User $user)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'amount' => 'required',
            ], [
                'amount.required' => __('custom_field_is_required', ['name' => __("amount")]),
            ]);

            $amount = commaToDot($request->amount);

            $user->update([
                "balance" => $user->balance + $amount
            ]);

            BalanceActivity::create([
                "user_id" => $user->id,
                "type" => "IN",
                "amount" => $amount,
                "model" => "admin",
                "model_id" => Auth::id()
            ]);

            DB::commit();
            return $this->successResponse("Kullanıcı bakiyesi başarıyla düzenlendi.");
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage());
        }
    }

    public function output(Request $request, User $user)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'amount' => 'required',
            ], [
                'amount.required' => __('custom_field_is_required', ['name' => __("amount")]),
            ]);

            $amount = commaToDot($request->amount);

            $user->update([
                "balance" => $user->balance - $amount
            ]);

            BalanceActivity::create([
                "user_id" => $user->id,
                "type" => "OUT",
                "amount" => $amount,
                "model" => "admin",
                "model_id" => Auth::id()
            ]);

            DB::commit();
            return $this->successResponse("Kullanıcı bakiyesi başarıyla düzenlendi.");
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage());
        }
    }
}
