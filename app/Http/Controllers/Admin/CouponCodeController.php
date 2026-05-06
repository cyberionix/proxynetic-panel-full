<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CouponCode;
use App\Models\Product;
use App\Models\TokenPool;
use App\Traits\AjaxResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CouponCodeController extends Controller
{
    use AjaxResponses;

    public function index()
    {
        $products = Product::orderBy('name')->get();
        return view("admin.pages.couponCodes.index", compact('products'));
    }

    public function ajax(Request $request)
    {
        $searchableColumns = [
            "id",
            "coupon_code",
            "amount",
            "end_date",
            'is_active'
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

        $query = DB::table('coupon_codes')
            ->whereRaw($whereSearch)
            ->orderByRaw($orderBy)
            ->skip($start)->take($length);


        $list = $query->get();
        $countTotalRecords = $query->count();

        $query = DB::table('coupon_codes')
            ->whereRaw($whereSearch)
            ->orderByRaw($orderBy);

        $countFilteredRecords = $query->count();
        $data = [];

        foreach ($list as $item) {
            $data[] = [
                "<span data-id='" . $item->id . "'>" . $item->id . "</span>",
                $item->coupon_code,
                $item->type == 'PERCENT' ? '%' . $item->amount : $item->amount . ' TL',
                convertDate($item->end_date),
                '<span class="badge badge-' . ($item->is_active ? 'success' : 'danger') . '">' . ($item->is_active ? 'Aktif' : 'Pasif') . '</span>',
                '<a href="#" class="btn btn-sm btn-light btn-flex btn-center btn-active-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">' . __("actions") . '
                                        <i class="ki-duotone ki-down fs-5 ms-1"></i></a>
                                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                                        <div class="menu-item px-3">
                                            <a href="javascript:void(0);" class="menu-link px-3 editBtn">' . __("edit") . '</a>
                                        </div>
                                        <div class="menu-item px-3">
                                            <a href="javascript:void(0);" class="menu-link px-3 deleteBtn">' . __("delete") . '</a>
                                        </div>
                                    </div>
                                   ',
            ];
        }

        $response = array(
            'recordsTotal'    => $countTotalRecords,
            'recordsFiltered' => $countFilteredRecords,
            'data'            => $data
        );
        echo json_encode($response);
    }

    public function find(Request $request)
    {
        $couponCode = CouponCode::findOrFail($request->id);
        $couponCode->end_date = convertDate($couponCode->end_date);
        return $this->successResponse('', ['data' => $couponCode]);
    }
    public function show(CouponCode $tokenPool)
    {
        return $this->successResponse('', ['data' => $tokenPool]);
    }

    public function store(Request $request)
    {
        $data = $request->only('is_active','coupon_code', 'amount', 'only_new_users', 'type', 'use_limit', 'is_active', 'product_ids');
        $data['end_date'] = convertDate($request->end_date);
        if (!$request->coupon_code || !$request->amount) {
            return $this->errorResponse();
        }

        $create = CouponCode::create($data);
        if ($create) {
            return $this->successResponse(__("created_response", ["name" => __("Kupon Kodu")]));
        } else {
            return $this->errorResponse();
        }
    }

    public function update(Request $request)
    {
        $couponCode = CouponCode::find($request->id);
        if (!$couponCode) {
            return $this->errorResponse();
        }

        $data = $request->only('is_active','coupon_code', 'amount', 'only_new_users', 'type', 'use_limit', 'is_active', 'product_ids');
        $data['end_date'] = convertDate($request->end_date);
        if (!isset($data['product_ids'])) $data['product_ids'] = [];
        if (!$request->coupon_code || !$request->amount) {
            return $this->errorResponse();
        }

        $couponCode->fill($data);
        if ($couponCode->save()) {
            return $this->successResponse(__("edited_response", ["name" => __("Kupon kodu")]));

        } else {
            return $this->errorResponse();
        }
    }

    public function delete(Request $request)
    {
        $couponCode = CouponCode::find($request->id);

        if (!$couponCode) {
            return $this->errorResponse(__("record_not_found"));
        }

        if ($couponCode->delete()) {
            return $this->successResponse(__("Kod başarıyla silindi."));
        }

        return $this->errorResponse();
    }

}
