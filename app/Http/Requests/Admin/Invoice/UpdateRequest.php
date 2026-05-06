<?php

namespace App\Http\Requests\Admin\Invoice;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'invoice_date' => 'required|date_format:' . defaultDateFormat(),
        ];
    }

    public function messages()
    {
        return [
            'invoice_date.required' => __('custom_field_is_required', ['name' => __('invoice_date')]),
        ];
    }
}
