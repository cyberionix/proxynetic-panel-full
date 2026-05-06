<?php

namespace App\Http\Requests\Portal\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreIdentityNumberRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'first_name' => 'required',
            'last_name' => 'required',
            'birth_date' => 'required|date_format:' . defaultDateFormat(),
            'identity_number' => [
                Rule::unique('users', 'identity_number')->where(function ($query) {
                    return $query->whereNotNull('identity_number_verified_at')->whereNull('deleted_at');
                })
            ],
        ];
    }

    public function messages()
    {
        return [
            'first_name.required' => __('custom_field_is_required', ['name' => __('first_name')]),
            'last_name.required' => __('custom_field_is_required', ['name' => __('last_name')]),
            'birth_date.required' => __('custom_invalid_date_format', ["name" => __("birth_date")]),
            'birth_date.date_format' => __('custom_invalid_date_format', ["name" => __("birth_date")]),
            'identity_number.required' => __('custom_invalid_date_format', ["name" => __("tc_identity_number")]),
            'identity_number.unique' => __('identity_number_field_must_be_unique'),
        ];
    }
}
