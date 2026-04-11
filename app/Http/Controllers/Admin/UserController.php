<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\CreateRequest;
use App\Http\Requests\Admin\User\StoreRequest;
use App\Http\Requests\Admin\User\UpdateRequest;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Traits\AjaxResponses;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class UserController extends Controller
{
    use AjaxResponses;

    public function index()
    {
        return view("admin.pages.users.index");
    }

    private function normalizeTurkishString($string)
    {
        $replacements = [
            "\xC4\xB0" => "İ", // İ => İ (Büyük I ve Üstüne Nokta)
            "İ"        => "İ",        // İ => İ (Büyük I ve Üstüne Nokta)
            "ı"        => "ı",         // ı => ı (Küçük I)
            "i"        => "i",         // i => i (Küçük i)
            "I"        => "I",         // I => I (Büyük I)
            "i"        => "i"          // i => i (Küçük i)
        ];

        // Replace all Turkish characters to their desired forms
        $normalizedString = strtr($string, $replacements);

        return $normalizedString;
    }

    public function ajax(Request $request)
    {
        $searchableColumns = [
            "users.id",
            db_user_full_name_expr('users'),
            "users.email",
            "users.id",
            "user_groups.name",
            "users.last_seen_at"
        ];

        $whereSearch = "users.deleted_at IS NULL ";

        if (isset($request->order[0]["column"]) and isset($request->order[0]["dir"])) {
            $orderBy = $searchableColumns[$request->order[0]["column"]] . " " . $request->order[0]["dir"];
        } else {
            $orderBy = "users.id DESC ";
        }

        $searchVal = $request->search["value"];

        $searchVal = $this->normalizeTurkishString($searchVal);

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

        $userGroups = $request->user_groups;
        if ($userGroups) {
            $userGroups = array_map(function ($item) {
                return '"' . $item . '"';
            }, $userGroups);
            $userGroups = implode(',', $userGroups);
            $whereSearch .= " AND users.user_group_id IN ($userGroups) ";
        }

        $start = $request->start ?? 0;
        $length = $request->length == -1 ? 10 : $request->length;

        $query = User::select('users.*', 'user_groups.name as user_group_name')
            ->leftJoin('user_groups', 'user_groups.id', '=', 'users.user_group_id');

        $query = $query->whereRaw($whereSearch)
            ->orderByRaw($orderBy);

        $countFilteredRecords = $query->count();
        $query->skip($start)->take($length);
        $list = $query->get();
        $countTotalRecords = $query->count();


        $data = [];
        foreach ($list as $item) {
            $userGroup = $item->user_group_name ? "<span class='badge badge-secondary badge-sm'>" . $item->user_group_name . "</span>" : "";
            $lastSeenAt = $item->last_seen_at ? "<span class='badge badge-secondary badge-sm'>" . $item->last_seen_at->format(defaultDateTimeFormat()) . "</span>" : "";
            $process = '<a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm user-action-btn" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                        <span class="d-none d-md-inline">İşlemler</span>
                        <i class="ki-duotone ki-down fs-5 ms-1 d-none d-md-inline"></i>
                        <i class="fa fa-ellipsis-vertical d-inline d-md-none"></i>
                        </a>
<div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">

    <div class="menu-item px-3">
        <a href="' . route("admin.users.show", ["user" => $item->id]) . '" class="menu-link px-3">
            ' . __("view") . '
        </a>
    </div>
    <div class="menu-item px-3">
        <a href="javascript:void(0);" class="menu-link px-3 deleteBtn">
            ' . __("delete") . '
        </a>
    </div>
</div>';
            $userUrl = route("admin.users.show", ["user" => $item->id]);
            $data[] = [
                "<span data-id='" . $item->id . "'>" . $item->id . "</span>",
                "<a href='" . $userUrl . "'>" . $item->full_name . "</a>",
                "<a href='" . $userUrl . "' class='text-gray-600 text-hover-primary'>" . $item->email . "</a>",
                $item->lastLoginIp(),
                $userGroup,
                $lastSeenAt,
                $process
            ];
        }

        $response = array(
            'recordsTotal'    => $countTotalRecords,
            'recordsFiltered' => $countFilteredRecords,
            'data'            => $data
        );
        echo json_encode($response);
    }

    public function store(StoreRequest $request)
    {
        $data = $request->only("first_name", "last_name", "email", "password", "user_group_id", "phone", "birth_date");
        $data["accept_sms"] = $request->accept_sms == 1 ? 1 : 0;
        $data["accept_email"] = $request->accept_email == 1 ? 1 : 0;
        $data["phone_verified_at"] = $request->phone_verify == 1 ? Carbon::now() : null;
        $data["email_verified_at"] = $request->email_verify == 1 ? Carbon::now() : null;
        $data["identity_number_verified_at"] = $request->identity_number_verify == 1 ? Carbon::now() : null;

        $create = User::create($data);
        if ($create) {
            return $this->successResponse(__("created_response", ["name" => __("customer")]));
        } else {
            return $this->errorResponse();
        }
    }

    public function find(User $user)
    {
        $user->load('address');
        return $this->successResponse("", ["data" => $user]);
    }

    public function show(User $user)
    {
        $user->load(["user_group"]);

        $stats = [
            "totalCollectAmount" => "0",
            "joinDate"           => $user->created_at->format(defaultDateFormat()),
            "lastSeenAt"         => $user->last_seen_at ? Carbon::diffText($user->last_seen_at) : "-",
        ];

        return view("admin.pages.users.details.index", compact(["user", "stats"]));
    }

    public function update(UpdateRequest $request, User $user)
    {
        $data = $request->only("first_name", "last_name", "email", "user_group_id", "phone", "birth_date");
        $data["accept_sms"] = $request->accept_sms == 1 ? 1 : 0;
        $data["accept_email"] = $request->accept_email == 1 ? 1 : 0;
        $data["phone_verified_at"] = $request->phone_verify == 1 ? Carbon::now() : null;
        $data["email_verified_at"] = $request->email_verify == 1 ? Carbon::now() : null;
        $data["identity_number_verified_at"] = $request->identity_number_verify == 1 ? Carbon::now() : null;

        $user->fill($data);
        if ($user->save()) return $this->successResponse(__("edited_response", ["name" => __("customer")]));
        return $this->errorResponse();
    }

    public function search(Request $request)
    {
        $term = $request->term["term"] ?? '';
        $relations = $request->relations;

        $result = [];
        $data = User::where(function ($q) use ($term) {
            $q->where('first_name', 'LIKE', '%' . $term . '%')
              ->orWhere('last_name', 'LIKE', '%' . $term . '%')
              ->orWhere('id', 'LIKE', '%' . $term . '%')
              ->orWhereRaw("(first_name || ' ' || last_name) LIKE ?", ['%' . $term . '%']);
        });
        if ($relations) {
            if (!is_array($relations)) $relations = [$relations];
            foreach ($relations as $relation) {
                $data->with($relation);
            }
        }
        $data = $data->limit(50)
            ->orderByDesc("id")
            ->get();

        foreach ($data as $item) {
            $result[] = [
                "id"          => $item->id,
                "name"        => $item->id . " | " . $item->first_name . ' ' . $item->last_name,
                "extraParams" => $item
            ];
        }

        return response()->json([
            "items" => $result
        ]);
    }

    public function resetPassword(Request $request)
    {
        $id = $request->get('id');
        $user = User::findOrFail($id);
        if ($user->resetPasswordViaSms()) {
            return $this->successResponse('Parola başarıyla sıfırlandı ve SMS iletildi.');
        }
        return $this->errorResponse(__("error_response"));

    }

    public function delete(User $user)
    {
        $user->email = $user->email.'-del-'.date('Ymd').'-'.date('His');
        $user->save();
        if ($user->delete()) {
            return $this->successResponse(__("deleted_response", ["name" => __("customer")]), ["redirectUrl" => route("admin.users.index")]);
        }
        return $this->errorResponse(__("error_response"));
    }

    public function banAccount(User $user)
    {
        $update = $user->update([
            "is_banned" => 1
        ]);
        if (!$update) return $this->errorResponse(__("error_response"));
        return $this->successResponse("Kullanıcı başarıyla yasaklandı.");
    }

    public function unbanAccount(User $user)
    {
        $update = $user->update([
            "is_banned" => 0
        ]);
        if (!$update) return $this->errorResponse(__("error_response"));
        return $this->successResponse("Kullanıcının yasaklanması başarıyla kaldırıldı.");
    }

    public function accountLogin(User $user)
    {
        $guard = Auth::guard('web');
        $guard->setUser($user);
        session()->put($guard->getName(), $user->getAuthIdentifier());

        return $this->successResponse(__('login_successful'), ["redirectUrl" => route("portal.dashboard")]);
    }

    public function getLastLoginIp(User $user)
    {
        return $this->successResponse("", [
            "ip" => $user->lastLoginIp()
        ]);
    }

    public function updateSecurity(Request $request, User $user)
    {
        $request->validate([
            'security.is_cant_vpn'              => 'sometimes|boolean',
            'security.is_limit_payment_methods' => 'sometimes|boolean',
            'security.payment_methods'          => 'required_if:security.is_limit_payment_methods,1|array',
            'security.is_no_support'            => 'sometimes|boolean',
            'security.is_limited_support'       => 'sometimes|boolean',
        ], [
            'security.payment_methods.required_if' => "En az bir ödeme yöntemi seçmelisiniz.",
        ]);

        $security = $request->input('security', []);

        $update = $user->security()->update([
            "is_cant_vpn"              => $security["is_cant_vpn"] ?? 0,
            "is_limit_payment_methods" => $security["is_limit_payment_methods"] ?? 0,
            "payment_methods"          => isset($security["is_limit_payment_methods"]) && $security["is_limit_payment_methods"] == 1 ? $security["payment_methods"] : null,
            "is_no_support"            => $security["is_no_support"] ?? 0,
            "is_limited_support"       => $security["is_limited_support"] ?? 0,
        ]);

        if (!$update) return $this->errorResponse(__("error_message"));
        return $this->successResponse(__("edited_response", ["name" => __("security")]));
    }
}


