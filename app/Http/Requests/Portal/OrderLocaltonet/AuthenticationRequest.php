<?php

namespace App\Http\Requests\Portal\OrderLocaltonet;

use Illuminate\Foundation\Http\FormRequest;

class AuthenticationRequest extends FormRequest
{
    public function rules(): array
    {
        if ($this->input('is_active')) {
            return [
                'user_name' => 'required|string|size:6',
                'password'  => 'required|string|size:6',
                'whitelist' => 'nullable',
            ];
        }

        return [
            'user_name' => 'nullable',
            'password'  => 'nullable',
            'whitelist' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'user_name.required' => __('custom_field_is_required', ['name' => __('user_name')]),
            'user_name.size'     => 'Kullanıcı adı tam olarak 6 karakter olmalıdır.',
            'password.required'  => __('custom_field_is_required', ['name' => __('password')]),
            'password.size'      => 'Parola tam olarak 6 karakter olmalıdır.',
            'whitelist.required' => 'Aktif kapalıyken en az bir geçerli IP adresi girilmelidir.',
        ];
    }
}
