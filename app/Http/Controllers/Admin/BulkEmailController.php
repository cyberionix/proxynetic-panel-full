<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\AjaxResponses;
use Illuminate\Http\Request;

class BulkEmailController extends Controller
{
    use AjaxResponses;

    public function index()
    {
        $monthFilterOptions = [];
        for ($i = 1; $i <= 48; $i++) {
            $monthFilterOptions[] = [
                "label" => $i . ". " . __("month"),
                "value" => $i,
            ];
        }
        return view("admin.pages.bulkEmail.index", compact("monthFilterOptions"));
    }

    public function findUsers($request)
    {
        $request->validate([
            "type" => "required",
            "mailSubject" => "required",
        ], [
            'type.required' => __('custom_field_is_required', ['name' => __("type")]),
            'mailSubject.required' => __('custom_field_is_required', ['name' => __("mail_subject")]),
        ]);

        if ($request->type == "userFilter") {
            $users = User::whereMonthOfSubs($request->monthFilter)
                ->whereHas('deliveryAddress', function ($query) use ($request) {
                    $query->where('city_id', $request->city_id);
                })
                ->whereHas('deliveryAddress', function ($query) use ($request) {
                    $query->where('city_id', $request->city_id);
                })->get();


            dd($users);
            $users = [];
        } else if ($request->type == "selectUser") {
            $users = User::select("id", "first_name", "last_name", "email")->whereIn("id", $request->user_id)->get();
        }
        return $users;
    }

    public function send(Request $request)
    {
        $users = $this->findUsers($request);
        return $this->successResponse("", ["users" => $users, "reload" => true]);
    }

    public function showUsers(Request $request)
    {
        $findUsers = $this->findUsers($request);
        $userCount = count($findUsers);

        $data = "";
        foreach ($findUsers as $key => $item) {
            if ($key == 0) $data .= "<ul class='list-group'>";
            $data .= "<li class='list-group-item'>#$item->id - $item->first_name $item->last_name - $item->email</li>";
            if ($key == $userCount - 1) $data .= "</ul>";
        }
        return $this->successResponse("<b>$userCount sonuç bulundu.</b><br>$data");

    }
}
