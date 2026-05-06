<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\UserAddress\StoreRequest;
use App\Http\Requests\Portal\UserAddress\UpdateRequest;
use App\Models\Appointment;
use App\Models\User;
use App\Models\UserAddress;
use App\Traits\AjaxResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserAddressController extends Controller
{
    use AjaxResponses;

    public function store(StoreRequest $request)
    {
        $user = Auth::user();
        DB::beginTransaction();
        try {
            $data = $request->only("title", "city_id", "district_id", "address", "invoice_type");
            if ($request->invoice_type == "CORPORATE"){
                $data["tax_number"] = $request->tax_number;
                $data["tax_office"] = $request->tax_office;
                $data["company_name"] = $request->company_name;
            } else if ($request->invoice_type == "INDIVIDUAL"){
                $data["tax_number"] = $request->identity_number;
            }

            $old_address = $user->address;
            $data["user_id"] = auth()->user()->id;
            $data["country_id"] = $request->country_id;
            if ($request->country_id != 1){
                $data['city_id'] = null;
                $data['district_id'] = null;
            }

            $create = UserAddress::create($data);

            $defInvoiceAddress = $request->default_invoice_address ?? false;
            if ($defInvoiceAddress || !$old_address || $user->default_invoice_address != $old_address->id) {
                $userData = [];
                    $userData["invoice_address_id"] = $create->id;
                User::whereId(Auth::id())->update($userData);
            }

            $create->load(["city", "district", "country"]);
            DB::commit();
            return $this->successResponse(__("added_response", ["name" => __("address")]), ["data" => $create]);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage());
        }
    }

    public function find(UserAddress $address)
    {
        if ($address->user_id != auth()->user()->id) return $this->errorResponse(__("invalid_request"));
        $address->load("district");

        return $this->successResponse("", ["data" => $address]);
    }

    public function update(UpdateRequest $request, UserAddress $address)
    {
        DB::beginTransaction();
        try {
            if ($address->user_id != auth()->user()->id) return $this->errorResponse(__("invalid_request"));
            $data = $request->only("title", "city_id", "district_id", "address", "invoice_type");
            if ($request->invoice_type == "CORPORATE"){
                $data["tax_number"] = $request->tax_number;
                $data["tax_office"] = $request->tax_office;
                $data["company_name"] = $request->company_name;
            } else if ($request->invoice_type == "INDIVIDUAL"){
                $data["tax_number"] = $request->identity_number;
            }

            $address->update($data);

            $defInvoiceAddress = $request->default_invoice_address ?? false;
            if ($defInvoiceAddress) {
                $userData = [];
                $userData["invoice_address_id"] = $address->id;
                User::whereId(Auth::user()->id)->update($userData);
            }
            if (!$defInvoiceAddress){
                User::whereId(Auth::user()->id)->whereInvoiceAddressId($address->id)->update(["invoice_address_id" => null]);
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
        if ($address->user_id != auth()->user()->id) return $this->errorResponse(__("invalid_request"));
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
}
