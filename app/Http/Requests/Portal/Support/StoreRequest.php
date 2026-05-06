<?php

namespace App\Http\Requests\Portal\Support;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'subject' => 'required|max:70',
            'department' => 'required',
            'priority' => 'required',
            'message' => 'required|max:1000',
        ];
    }

    public function messages()
    {
        return [
            'subject.required' => __('custom_field_is_required', ['name' => __('subject')]),
            'subject.max' => __('custom_field_max_char_size', ['name' => __('subject'), 'size' => "70"]),
            'department.required' => __('custom_field_is_required', ['name' => __('department')]),
            'priority.required' => __('custom_field_is_required', ['name' => __('priority')]),
            'message.required' => __('custom_field_is_required', ['name' => __('message')]),
            'message.max' => __('custom_field_max_char_size', ['name' => __('message'), 'size' => "1000"]),
        ];
    }
}
