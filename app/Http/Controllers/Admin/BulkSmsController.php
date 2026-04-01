<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\BulkSmsNotification;
use App\Traits\AjaxResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class BulkSmsController extends Controller
{
    use AjaxResponses;

    public function index()
    {
        $monthFilterOptions = [];
        for ($i=1;$i<=48;$i++){
            $monthFilterOptions[] = [
                "label" => $i . ". " . __("month"),
                "value" => $i,
            ];
        }
        return view("admin.pages.bulkSms.index", compact("monthFilterOptions"));
    }

    public function send(Request $request)
    {
        $request->validate([
            "type" => "required",
            "text" => "required",
        ], [
            'type.required' => __('custom_field_is_required', ['name' => __("type")]),
            'text.required' => __('custom_field_is_required', ['name' => __("sms_text")]),
            ]);

        if ($request->type == "userFilter"){

            if ($request->statusFilter == 'ACTIVE') {
                $users = User::whereHas('subscription')->get();
            }else{
                $users = User::all();
            }
            Notification::send($users, new BulkSmsNotification($request->text));

            return [
                'success' => true,
                'message' => 'SMSler başarıyla gönderildi.'
            ];

        } else if ($request->type == "selectUser"){
            $users = User::select("phone")->whereIn("id", $request->user_id)->get();
            Notification::send($users,new BulkSmsNotification($request->text));

            return [
                'success' => true,
                'message' => 'SMSler başarıyla gönderildi.'
            ];
        }

        return $this->errorResponse("", ["users" => $users]);
    }
}
