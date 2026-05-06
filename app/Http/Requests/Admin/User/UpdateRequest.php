<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->where(function ($query) {
                    $query->whereNull('deleted_at');
                })->ignore($this->user->id),
            ],
            'birth_date' => 'nullable|date_format:' . defaultDateFormat(),
        ];
    }

    public function messages()
    {
        return [
            'first_name.required' => __('custom_field_is_required', ['name' => __('first_name')]),
            'last_name.required' => __('custom_field_is_required', ['name' => __('last_name')]),
            'email.required' => __('custom_field_is_required', ['name' => __('email')]),
            'email.email' => __('the_value_is_not_a_valid_email_address'),
            'email.unique' => __('the_email_has_already_been_taken'),
            'birth_date.date_format' => __('custom_invalid_date_format', ["name" => __("birth_date")]),
        ];
    }
}
