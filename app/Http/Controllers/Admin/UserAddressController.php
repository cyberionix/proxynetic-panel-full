<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\UserAddress\StoreRequest;
use App\Http\Requests\Portal\UserAddress\UpdateRequest;
use App\Models\User;
use App\Models\UserAddress;
use App\Traits\AjaxResponses;
use Illuminate\Support\Facades\DB;

class UserAddressController extends Controller
{
    use AjaxResponses;

    public function store(StoreRequest $request, User $user)
    {
        DB::beginTransaction();
        try {
            $data = $request->only("title", "city_id", "district_id", "address", "invoice_type");
            if ($request->invoice_type == "CORPORATE") {
                $data["tax_number"] = $request->tax_number;
                $data["tax_office"] = $request->tax_office;
                $data["company_name"] = $request->company_name;
            } else if ($request->invoice_type == "INDIVIDUAL") {
                $data["tax_number"] = $request->identity_number;
            }
            $data["user_id"] = $user->id;
            $data["country_id"] = 1;
            $create = UserAddress::create($data);

            $defInvoiceAddress = $request->default_invoice_address ?? false;
            if ($defInvoiceAddress) {
                $userData = [];
                $userData["invoice_address_id"] = $create->id;

                User::whereId($user->id)->update($userData);
            }

            DB::commit();
            return $this->successResponse(__("added_response", ["name" => __("address")]));
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage());
        }
    }

    public function find(UserAddress $address)
    {
        $address->load("district");
        return $this->successResponse("", ["data" => $address]);
    }

    public function update(UpdateRequest $request, UserAddress $address)
    {
        DB::beginTransaction();
        try {
            $data = $request->only("title", "city_id", "district_id", "address", "invoice_type");
            if ($request->invoice_type == "CORPORATE") {
                $data["tax_number"] = $request->tax_number;
                $data["tax_office"] = $request->tax_office;
                $data["company_name"] = $request->company_name;
            } else if ($request->invoice_type == "INDIVIDUAL") {
                $data["tax_number"] = $request->identity_number;
            }
            $address->update($data);

            $defInvoiceAddress = $request->default_invoice_address ?? false;
            if ($defInvoiceAddress) {
                $userData = [];
                    $userData["invoice_address_id"] = $address->id;

                User::whereId($address->user_id)->update($userData);
            }
            if (!$defInvoiceAddress) {
                User::whereId($address->user_id)->whereInvoiceAddressId($address->id)->update(["invoice_address_id" => null]);
            }
            DB::commit();
            return $this->successResponse(__("edited_response", ["name" => __("address")]));
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage());
        }
    }

    public function delete(UserAddress $address)
    {
        DB::beginTransaction();
        try {
            $address->delete();
            User::where('invoice_address_id', $address->id)->update(['invoice_address_id' => null]);

            DB::commit();
            return $this->successResponse(__("deleted_response", ["name" => __("address")]));
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage());
        }
    }

    public function search(User $user)
    {
        $term = isset($request->term["term"]) ? $request->term["term"] : '';
        $data = UserAddress::whereUserId($user->id)->where('id', 'LIKE', '%' . $term . '%')->get();
        $result = [];
        foreach ($data as $item) {
            $result[] = [
                "id"   => $item->id,
                "name" => $item->title
            ];
        }

        return response()->json([
            "items" => $result
        ]);
    }
}
