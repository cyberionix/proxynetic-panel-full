<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SmsLog;
use App\Models\Support;
use App\Traits\AjaxResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SupportController extends Controller
{
    use AjaxResponses;

    public function index()
    {
        return view("admin.pages.supports.index");
    }
    public function ajax(Request $request)
    {
        $userFullNameExpr = db_user_full_name_expr('users');

        $whereSearch = 'supports.deleted_at IS NULL ';
        $showAllList = $request->boolean('showAllList', true);
        if ($showAllList) {
            $searchableColumns = [
                'supports.id',
                $userFullNameExpr,
                'supports.subject',
                'supports.department',
                'supports.updated_at',
                'supports.status',
                'supports.id',
            ];
        } else {
            $searchableColumns = [
                'supports.id',
            ];

            $userId = $request->userId;
            if ($userId) {
                $whereSearch .= ' AND supports.user_id = '.(int) $userId.' ';
            }
        }

        $orderBy = 'supports.id DESC';
        if (isset($request->order[0]['column'], $request->order[0]['dir'])) {
            $col = (int) $request->order[0]['column'];
            if (isset($searchableColumns[$col])) {
                $orderBy = $searchableColumns[$col].' '.$request->order[0]['dir'];
            }
        }

        $searchVal = $request->input('search.value');
        if ($searchVal) {
            $whereSearch .= ' AND (';
            foreach ($searchableColumns as $key => $searchableColumn) {
                $whereSearch .= "$searchableColumn LIKE '%{$searchVal}%'";
                if (array_key_last($searchableColumns) != $key) {
                    $whereSearch .= ' OR ';
                } else {
                    $whereSearch .= ')';
                }
            }
        }

        $status = $request->input('status');
        if ($status) {
            $whereSearch .= " AND supports.status = '".addslashes($status)."' ";
        }

        $start = (int) ($request->start ?? 0);
        $length = $request->length == -1 ? 10 : (int) $request->length;

        // Yönetici listesi: müşteri global scope'unu kullanma (Auth::id() web guard ile null kalıp filtreyi bozabiliyordu).
        $makeQuery = function () use ($whereSearch, $orderBy, $userFullNameExpr) {
            return Support::withoutGlobalScope('for_user')
                ->select(
                    'supports.*',
                    DB::raw($userFullNameExpr.' as user_name'),
                )
                ->leftJoin('users', 'users.id', '=', 'supports.user_id')
                ->whereRaw($whereSearch)
                ->orderByRaw($orderBy);
        };

        $countFilteredRecords = $makeQuery()->count();
        $list = $makeQuery()->offset($start)->limit($length)->get();

        $data = [];
        foreach ($list as $item) {
            $subject = "<a href='".route('admin.supports.show', ['support' => $item->id])."'>".$item->subject.'</a>';
            if ($showAllList) {
                $data[] = [
                    "<span data-id='".$item->id."' class='badge badge-sm badge-light-primary'>#".$item->id.'</span>',
                    "<a target='_blank' href='".route('admin.users.show', ['user' => $item->user_id])."'>".e($item->user_name).'</a>',
                    $subject,
                    __(strtolower($item->department)),
                    $item->updated_at->format(defaultDateTimeFormat()),
                    $item->drawStatusBadge(),
                    '<a href="'.route('admin.supports.show', ['support' => $item->id]).'" class="btn btn-light-primary btn-sm">'.__('view').'</a>',
                ];
            }
        }

        return response()->json([
            'recordsTotal' => $countFilteredRecords,
            'recordsFiltered' => $countFilteredRecords,
            'data' => $data,
        ]);
    }
    public function show(Support $support)
    {
        return view("admin.pages.supports.show.index", compact("support"));
    }
    public function find(Support $support)
    {
        $support->load("messages");
        return $this->successResponse("", ["data" => $support]);
    }
    public function saveMessage(Support $support, Request $request)
    {
        $request->validate([
            "message" => "required",
        ], [
            "message.required" => __("custom_field_is_required", ["name" => __("message")]),
        ]);

        $support->update([
           "status" => "ANSWERED"
        ]);

        $save = $support->messages()->create([
            "message" => $request->message,
            "admin_id" => Auth::id()
        ]);

        if ($save) {
            return $this->successResponse(__("message_delivered"));
        }
        return $this->errorResponse(__("error_response"));
    }
    public function updateStatus(Support $support, Request $request)
    {
        $request->validate([
            'value' => ['required', Rule::in(['WAITING_FOR_AN_ANSWER', 'ANSWERED', 'RESOLVED'])],
        ],[
            'value.required' => __('custom_field_is_required', ['name' => __('status')]),
            'value.in' => 'Geçersiz değer. Sayfayı yenileyip tekrar deneyiniz.',
        ]);

        $save =  $support->update([
            "status" => $request->value
        ]);
        if (!$save) return $this->errorResponse(__("error_response"));
        return $this->successResponse(__("edited_response", ["name" => __("status")]));
    }
    public function updateDepartment(Support $support, Request $request)
    {
        $request->validate([
            'value' => ['required', Rule::in(['GENERAL', 'ORDER', 'ACCOUNTING', 'TECHNICAL_SUPPORT'])],
        ],[
            'value.required' => __('custom_field_is_required', ['name' => __('status')]),
            'value.in' => 'Geçersiz değer. Sayfayı yenileyip tekrar deneyiniz.',
        ]);

        $save = $support->update([
            "department" => $request->value
        ]);
        if (!$save) return $this->errorResponse(__("error_response"));
        return $this->successResponse(__("edited_response", ["name" => __("department")]));
    }
    public function lock(Support $support)
    {
        $save = $support->update([
            "is_locked" => 1
        ]);
        if (!$save) return $this->errorResponse(__("error_response"));
        return $this->successResponse("Destek talebi kilitlendi.");
    }
    public function unlock(Support $support)
    {
        $save = $support->update([
            "is_locked" => 0
        ]);
        if (!$save) return $this->errorResponse(__("error_response"));
        return $this->successResponse("Destek talebi kilidi açıldı.");
    }
    public function delete(Support $support)
    {
        $save = $support->delete();
        if (!$save) return $this->errorResponse(__("error_response"));
        return $this->successResponse("Destek talebi başarıyla silindi.", ["redirectUrl" => route("admin.supports.index")]);
    }
}
