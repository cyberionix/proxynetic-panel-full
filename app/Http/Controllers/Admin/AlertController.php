<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Traits\AjaxResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AlertController extends Controller
{
    use AjaxResponses;

    public function index()
    {
        return view("admin.pages.alerts.index");
    }

    public function ajax(Request $request)
    {
        $searchableColumns = [
            "id",
            "message",
            "bg_color",
            "start_date",
            "end_date"
        ];

        $whereSearch = "deleted_at IS NULL ";

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

        $query = Alert::whereRaw($whereSearch)
            ->orderByRaw($orderBy);

        $countFilteredRecords = $query->count();
        $query = $query->skip($start)->take($length);

        $list = $query->get();
        $countTotalRecords = $query->count();
        $data = [];

        foreach ($list as $item) {
            $data[] = [
                $item->id,
                $item->message,
                "<span class='badge badge-" . $item->bg_color . " w-80px h-30px'> </span>",
                "<span class='badge badge-secondary'>" . $item->draw_start_date . "</span>",
                "<span class='badge badge-secondary'>" . $item->draw_end_date . "</span>",
                "<button class='btn btn-light-primary btn-sm me-1 editAlertBtn' data-find-url='" . route("admin.alerts.find", ["alert" => $item->id]) . "' data-update-url='" . route("admin.alerts.update", ["alert" => $item->id]) . "'>" . __("edit") . "</button><button class='btn btn-danger btn-sm deleteAlertBtn' data-delete-url='" . route("admin.alerts.delete", ["alert" => $item->id]) . "'>" . __("delete") . "</button>"
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
        $request->validate([
            'message' => 'required',
            'start_date' => 'required|date_format:' . defaultDateFormat(),
            'end_date' => 'required|date_format:' . defaultDateFormat(),
            'bg_color' => Rule::in(['success', 'primary', 'danger']),
        ],[
            'message.required' => __('custom_field_is_required', ['name' => __('message')]),
            'start_date.date_format' => __('custom_invalid_date_format', ["name" => __("start_date")]),
            'end_date.date_format' => __('custom_invalid_date_format', ["name" => __("end_date")]),
            'bg_color.in' => "Arka plan renkleri geçersiz. Sayfayı yenileyip tekrar deneyiniz.",
        ]);

        $data = $request->only(["message", "start_date", "end_date", "bg_color"]);
        $create = Alert::create($data);
        if (!$create){
            return $this->errorResponse(__("your_details_are_incorrect") . ". " . __("please_try_again"));
        }
        return $this->successResponse(__("created_response", ["name" => __("alert")]));
    }

    public function find(Alert $alert)
    {
        return $this->successResponse("", ["data" => $alert]);
    }

    public function update(Request $request, Alert $alert)
    {
        $request->validate([
            'message' => 'required',
            'start_date' => 'required|date_format:' . defaultDateFormat(),
            'end_date' => 'required|date_format:' . defaultDateFormat(),
            'bg_color' => Rule::in(['success', 'primary', 'danger']),
        ],[
            'message.required' => __('custom_field_is_required', ['name' => __('message')]),
            'start_date.date_format' => __('custom_invalid_date_format', ["name" => __("start_date")]),
            'end_date.date_format' => __('custom_invalid_date_format', ["name" => __("end_date")]),
            'bg_color.in' => "Arka plan renkleri geçersiz. Sayfayı yenileyip tekrar deneyiniz.",
        ]);

        $data = $request->only(["message", "start_date", "end_date", "bg_color"]);
        $update = $alert->update($data);
        if (!$update){
            return $this->errorResponse(__("your_details_are_incorrect") . ". " . __("please_try_again"));
        }
        return $this->successResponse(__("edited_response", ["name" => __("alert")]));
    }

    public function delete(Alert $alert)
    {
        if ($alert->delete()){
            return $this->successResponse(__("deleted_response", ["name" => __("alert")]));
        }
        return $this->errorResponse(__("your_details_are_incorrect") . ". " . __("please_try_again"));
    }
}
