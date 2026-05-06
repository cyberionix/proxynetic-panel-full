<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TokenPool;
use App\Models\UserGroup;
use App\Traits\AjaxResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TokenPoolController extends Controller
{
    use AjaxResponses;
    public function index()
    {

        return view('admin.pages.tokenPools.index');
    }
    public function ajax(Request $request)
    {
        $searchableColumns = [
            "id",
            "name",
            "created_At"
        ];

        $whereSearch = "deleted_at IS NULL";

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

        $query = DB::table('token_pools')
            ->whereRaw($whereSearch)
            ->orderByRaw($orderBy)
            ->skip($start)->take($length);


        $list = $query->get();
        $countTotalRecords = $query->count();

        $query = DB::table('token_pools')
            ->whereRaw($whereSearch)
            ->orderByRaw($orderBy);

        $countFilteredRecords = $query->count();
        $data = [];

        foreach ($list as $item) {
            if(!$item->tokens) $item->tokens = '[]';

            $data[] = [
                "<span data-name='".$item->name."' data-tokens='".implode(',',json_decode($item->tokens,true))."' data-id='".$item->id."'>".$item->id."</span>",
                $item->name,
                convertDateTime($item->created_at),
                count(json_decode($item->tokens)),
                '<a href="#" class="btn btn-sm btn-light btn-flex btn-center btn-active-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">'.__("actions").'
                                        <i class="ki-duotone ki-down fs-5 ms-1"></i></a>
                                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                                        <div class="menu-item px-3">
                                            <a href="javascript:void(0);" class="menu-link px-3 editBtn">'.__("edit").'</a>
                                        </div>
                                        <div class="menu-item px-3">
                                            <a href="javascript:void(0);" class="menu-link px-3 deleteBtn">'.__("delete").'</a>
                                        </div>
                                    </div>
                                   ',
            ];
        }

        $response = array(
            'recordsTotal' => $countTotalRecords,
            'recordsFiltered' => $countFilteredRecords,
            'data' => $data
        );
        echo json_encode($response);
    }

    public function store(Request $request)
    {
        if (!$request->name){
            return $this->errorResponse();
        }

        $auth_tokens = $request->auth_tokens;
        $create = TokenPool::create(["name" => $request->name,'tokens' => $auth_tokens]);
        if ($create)
            return $this->successResponse('Kayıt başarıyla oluşturuldu.');
        return $this->errorResponse();
    }

    public function update(TokenPool $tokenPool, Request $request)
    {
        if (!$request->name){
            return $this->errorResponse();
        }

        $auth_tokens = $request->auth_tokens;
        $tokenPool->fill(["name" => $request->name,'tokens' => $auth_tokens]);
        if ($tokenPool->save())
            return $this->successResponse('Değişiklikler başarıyla kaydedildi.');
        return $this->errorResponse();
    }

    public function delete(TokenPool $tokenPool)
    {
        if ($tokenPool->delete()) {
            return $this->successResponse('Kayıt başarıyla silindi.');
        }

        return $this->errorResponse();
    }
}
