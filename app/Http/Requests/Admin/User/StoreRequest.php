<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
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
                })
            ],
            'password' => ['required', 'min:8', 'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/'],
            'birth_date' => 'date_format:' . defaultDateFormat(),
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
            'password.required' => __('custom_field_is_required', ['name' => __('password')]),
            'password.min' => __('the_password_must_be_at_least_8_characters'),
            'password.regex' => __('the_password_must_contain_at_least_one_letter_and_one_number'),
            'birth_date.date_format' => __('custom_invalid_date_format', ["name" => __("birth_date")]),
        ];
    }
}
