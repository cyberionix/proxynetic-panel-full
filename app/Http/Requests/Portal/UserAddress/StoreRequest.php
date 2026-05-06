<?php

namespace App\Http\Requests\Portal\UserAddress;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
//            'title' => 'required',
            'country_id' => 'required',
            'city_id' => [
                Rule::requiredIf(function () {
                    return $this->input('country_id') == 1;
                })
            ],
            'district_id' => [
                Rule::requiredIf(function () {
                    return $this->input('country_id') == 1;
                })
            ],
            'address' => 'required',
            'invoice_type' => 'required',
            'identity_number' => [
                Rule::requiredIf(function () {
                    return $this->input('country_id') == 1;
                }),
                'sometimes:numeric',
                'sometimes:digits:11'
            ],
            'tax_number' => Rule::requiredIf(function () {
                return $this->input('invoice_type') === 'CORPORATE';
            }),
            'tax_office' => Rule::requiredIf(function () {
                return $this->input('invoice_type') === 'CORPORATE';
            }),
            'company_name' => Rule::requiredIf(function () {
                return $this->input('invoice_type') === 'CORPORATE';
            }),
        ];
    }

    public function messages()
    {
        return [
            'title.required' => __('custom_field_is_required', ['name' => __('title')]),
            'city_id.required' => __('custom_field_is_required', ['name' => __('city')]),
            'district_id.required' => __('custom_field_is_required', ['name' => __('district')]),
            'address.required' => __('custom_field_is_required', ['name' => __('address')]),
            'invoice_type.required' => __('custom_field_is_required', ['name' => __('invoice_type')]),
            'identity_number.required' => __('custom_field_is_required', ['name' => __('tc_identity_number')]),
            'identity_number.numeric' => __("enter_a_valid_tc_id_number"),
            'identity_number.digits' => __("enter_a_valid_tc_id_number"),
            'tax_number.required' => __('custom_field_is_required', ['name' => __('tax_number')]),
            'tax_office.required' => __('custom_field_is_required', ['name' => __('tax_office')]),
            'company_name.required' => __('custom_field_is_required', ['name' => __('company_name')]),
        ];
    }
}
