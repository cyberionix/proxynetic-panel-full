<?php

namespace App\Http\Requests\Portal;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function rules()
    {
        return [
            'firstName'        => 'required',
            'lastName'         => 'required',
            'email'            => [
                'required',
                'email',
                Rule::unique('users')->where(function ($query) {
                    return $query->whereNull('deleted_at');
                }),
            ],
            'password'         => ['required', 'min:8', 'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/'],
            'confirm-password' => 'required|same:password',
            'toc'              => 'required',
        ];
    }

    public function messages()
    {
        return [
            'firstName.required'        => __('custom_field_is_required',['name' => __('firstname')]),
            'lastName.required'         => __('custom_field_is_required',['name' => __('lastname')]),
            'email.required'            => __('custom_field_is_required',['name' => __('email')]),
            'email.email'               => __('the_value_is_not_a_valid_email_address'),
            'email.unique'              => __('the_email_has_already_been_taken'),
            'password.required'         => __('custom_field_is_required',['name' => __('password')]),
            'password.min'              => __('the_password_must_be_at_least_8_characters'),
            'password.regex'              => __('the_password_must_contain_at_least_one_letter_and_one_number'),
            'confirm-password.required' => __('custom_field_is_required',['name' => __('repeat_password')]),
            'confirm-password.same'     => __('the_password_confirmation_does_not_match'),
            'toc.required'              => __('you_must_accept_terms'),
        ];
    }
}
