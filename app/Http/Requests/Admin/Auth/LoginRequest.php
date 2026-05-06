<?php

namespace App\Http\Requests\Admin\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email'
            ],
            'password' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'email.required' => __('custom_field_is_required', ['name' => __('email')]),
            'email.email' => __('the_value_is_not_a_valid_email_address'),
            'password.required' => __('custom_field_is_required', ['name' => __('password')]),
        ];
    }
}
