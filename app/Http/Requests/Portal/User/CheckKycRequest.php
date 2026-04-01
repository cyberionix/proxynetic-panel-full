<?php

namespace App\Http\Requests\Portal\User;

use Illuminate\Foundation\Http\FormRequest;

class CheckKycRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'card_front_side' => 'required|image|max:6144',
            'card_back_side' => 'required|image|max:6144',
            'selfie' => 'required|image|max:6144',
        ];
    }

    public function messages()
    {
        return [
            'card_front_side.required' => __('custom_field_is_required', ['name' => "Kimlik Ön Yüz"]),
            'card_front_side.image' => __('custom_field_is_image', ['name' => "Kimlik Ön Yüz"]),
            'card_front_side.max' => __('custom_field_max_size', ['name' => "Kimlik Ön Yüz", 'size' => '6 MB']),
            'card_back_side.required' => __('custom_field_is_required', ['name' => "Kimlik Arka Yüz"]),
            'card_back_side.image' => __('custom_field_is_image', ['name' => "Kimlik Arka Yüz"]),
            'card_back_side.max' => __('custom_field_max_size', ['name' => "Kimlik Arka Yüz", 'size' => '6 MB']),
            'selfie.required' => __('custom_field_is_required', ['name' => "Özçekim"]),
            'selfie.image' => __('custom_field_is_image', ['name' => "Özçekim"]),
            'selfie.max' => __('custom_field_max_size', ['name' => "Özçekim", 'size' => '6 MB']),
        ];
    }
}
