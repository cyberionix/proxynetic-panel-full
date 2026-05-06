<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BulkNotification;
use App\Traits\AjaxResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BulkNotificationController extends Controller
{
    use AjaxResponses;

    public function index()
    {
        return view("admin.pages.bulkNotifications.index");
    }

    public function ajax(Request $request)
    {
        $whereSearch = "bulk_notifications.deleted_at IS NULL ";
        $searchableColumns = [
            "bulk_notifications.id",
            "bulk_notifications.title",
            "bulk_notifications.created_at"
        ];

        if (isset($request->order[0]["column"]) and isset($request->order[0]["dir"])) {
            $orderBy = $searchableColumns[$request->order[0]["column"]] . " " . $request->order[0]["dir"];
        } else {
            $orderBy = "bulk_notifications.id DESC";
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

        $query = BulkNotification::whereRaw($whereSearch)
            ->orderByRaw($orderBy)
            ->skip($start)->take($length);

        $list = $query->get();

        $countTotalRecords = $query->count();

        $query = BulkNotification::whereRaw($whereSearch)
            ->orderByRaw($orderBy);

        $countFilteredRecords = $query->count();
        $data = [];

        foreach ($list as $item) {
            $data[] = [
                "<span data-id='" . $item->id . "' class='badge badge-sm badge-light-primary'>#" . $item->id . "</span>",
                $item->title,
                '<span class="badge badge-secondary badge-sm">' . $item->created_at->format(defaultDateTimeFormat()) . '</span>',
                $item->reader_ids ? count($item->reader_ids) : 0,
                '<a href="#" class="btn btn-sm btn-light btn-flex btn-center btn-active-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">' . __("actions") . '
                                        <i class="ki-duotone ki-down fs-5 ms-1"></i></a>
                                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                                        <div class="menu-item px-3">
                                            <a href="'. route("admin.bulkNotifications.edit", ["bulkNotification" => $item->id]) .'" class="menu-link px-3">' . __("edit") . '</a>
                                        </div>
                                        <div class="menu-item px-3">
                                            <a href="javascript:void(0);" class="menu-link px-3 deleteBtn">' . __("delete") . '</a>
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

    public function create()
    {
        return view("admin.pages.bulkNotifications.create.index");
    }

    public function store(Request $request)
    {
        $request->validate([
            "title" => "required",
        ], [
            'title.required' => __('custom_field_is_required', ['name' => __("title")]),
        ]);

        $create = BulkNotification::create([
            "title" => $request->title,
            "message" => $request->message ?? null
        ]);
        if ($create) {
            return $this->successResponse(__("created_response", ["name" => __("notification")]), ["redirectUrl" => route("admin.bulkNotifications.index")]);
        }
        return $this->errorResponse("Bir sorun oluştu.");
    }

    public function edit(BulkNotification $bulkNotification)
    {
        return view("admin.pages.bulkNotifications.edit.index", compact("bulkNotification"));
    }

    public function update(Request $request, BulkNotification $bulkNotification)
    {
        $request->validate([
            "title" => "required",
        ], [
            'title.required' => __('custom_field_is_required', ['name' => __("title")]),
        ]);

        $bulkNotification->fill([
            "title" => $request->title,
            "message" => $request->message ?? null
        ]);
        if ($bulkNotification->save()) {
            return $this->successResponse(__("created_response", ["name" => __("notification")]));
        }
        return $this->errorResponse("Bir sorun oluştu.");
    }

    public function delete(BulkNotification $bulkNotification)
    {
        if ($bulkNotification->delete()) {
            return $this->successResponse(__("deleted_response", ["name" => __("notification")]));
        }

        return $this->errorResponse();
    }
}
