<?php

namespace App\Http\Requests\Portal;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LoginRequest extends FormRequest
{
    public function rules()
    {
        return [
            'email' => [
                'required'
            ],
            'password' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'email.required' => __('custom_field_is_required', ['name' => __('email')]),
            'password.required' => __('custom_field_is_required', ['name' => __('password')]),
        ];
    }
}
