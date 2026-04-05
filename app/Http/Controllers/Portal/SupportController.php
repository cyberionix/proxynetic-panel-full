<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\Support\StoreRequest;
use App\Models\Support;
use App\Models\SupportMessage;
use App\Services\SupportAutoReplyService;
use App\Traits\AjaxResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SupportController extends Controller
{
    use AjaxResponses;

    public function index()
    {
        SupportAutoReplyService::processPendingAutoReplies();
        return view("portal.pages.supports.index");
    }

    public function ajax(Request $request)
    {
        $whereSearch = "supports.deleted_at IS NULL AND supports.user_id = " . Auth::id() . " ";
        $searchableColumns = [
            "supports.id",
            "supports.id",
            "supports.id",
            "supports.id",
            "supports.id",
            "supports.id"
        ];

        if (isset($request->order[0]["column"]) and isset($request->order[0]["dir"])) {
            $orderBy = $searchableColumns[$request->order[0]["column"]] . " " . $request->order[0]["dir"];
        } else {
            $orderBy = "supports.id DESC";
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

        $query = Support::select(
            'supports.*'
        )
            ->whereRaw($whereSearch)
            ->orderByRaw($orderBy);
        $countFilteredRecords = $query->count();

        $query = $query->skip($start)->take($length);

        $list = $query->get();
        $countTotalRecords = $query->count();

        $data = [];

        foreach ($list as $item) {
            $data[] = [
                "<span data-id='" . $item->id . "' class='badge badge-sm badge-light-primary'>#" . $item->id . "</span>",
                $item->subject,
                $item->drawDepartment,
                $item->updated_at->format(defaultDateTimeFormat()),
                $item->drawStatusBadge(),
                "<a href='" . route("portal.supports.show", ["support" => $item->id]) . "' class='btn btn-light-primary btn-sm'>" . __("view") . "</a>"

            ];
        }

        $latestUpdatedAt = Support::max('updated_at');
        $latestUpdatedAt = $latestUpdatedAt ? strtotime($latestUpdatedAt) : 0;

        $response = array(
            'recordsTotal' => $countTotalRecords,
            'recordsFiltered' => $countFilteredRecords,
            'data' => $data,
            'latestUpdatedAt' => $latestUpdatedAt,
        );
        echo json_encode($response);
    }

    public function store(StoreRequest $request)
    {
        $data = $request->only("subject", "department", "order_id", "priority");
        $data["user_id"] = Auth::user()->id;

        DB::beginTransaction();
        try {
            $support = Support::create($data);

            $create = SupportMessage::create([
                "message" => $request->message,
                "support_id" => $support->id
            ]);

            DB::commit();
            return $this->successResponse(__("created_response", ["name" => __("support_ticket")]), ["redirectUrl" => route("portal.supports.show", ["support" => $support->id])]);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage());
        }
    }

    public function show(Support $support)
    {
        SupportAutoReplyService::processPendingAutoReplies();
        return view("portal.pages.supports.show.index", compact("support"));
    }

    public function find(Support $support)
    {
        SupportAutoReplyService::processPendingAutoReplies();

        SupportMessage::where('support_id', $support->id)
            ->whereNotNull('admin_id')
            ->whereNull('seen_at')
            ->update(['seen_at' => now()]);

        $support->load("messages");
        return $this->successResponse("", ["data" => $support]);
    }

    public function saveMessage(Support $support, Request $request)
    {
        $request->validate([
            "message" => "required|max:1000",
            "file" => "nullable|file|mimes:jpg,jpeg,png|max:5120",
        ], [
            "message.required" => __("custom_field_is_required", ["name" => __("message")]),
            'message.max' => __('custom_field_max_char_size', ['name' => __('message'), 'size' => "1000"]),
            'file.mimes' => 'Sadece JPG, JPEG, PNG formatları desteklenir.',
            'file.max' => 'Dosya boyutu en fazla 5MB olabilir.',
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('support-attachments', 'public');
        }

        $support->update([
            "status" => "WAITING_FOR_AN_ANSWER"
        ]);

        $save = $support->messages()->create([
            "message" => $request->message,
            "file" => $filePath,
        ]);

        if ($save) {
            return $this->successResponse(__("message_delivered"));
        }
        return $this->errorResponse(__("error_response"));
    }

    public function typing(Support $support)
    {
        Cache::put("support_typing_user_{$support->id}", Auth::user()->full_name, now()->addSeconds(5));
        return response()->json(['success' => true]);
    }

    public function pollMessages(Support $support)
    {
        SupportMessage::where('support_id', $support->id)
            ->whereNotNull('admin_id')
            ->whereNull('seen_at')
            ->update(['seen_at' => now()]);

        $support->load('messages');
        $lastMessageId = $support->messages->first()?->id ?? 0;
        $isAdminTyping = Cache::get("support_typing_admin_{$support->id}");

        return response()->json([
            'success' => true,
            'last_message_id' => $lastMessageId,
            'data' => $support,
            'is_admin_typing' => $isAdminTyping ? true : false,
            'typing_admin_name' => $isAdminTyping ?: null,
        ]);
    }
}
