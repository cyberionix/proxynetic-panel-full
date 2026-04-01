<?php

namespace App\Http\Requests\Admin\Invoice;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'invoice_date' => 'required|date_format:' . defaultDateFormat(),
            'invoice_number' => [
                'required',
                Rule::unique('invoices')->where(function ($query) {
                    $query->whereNull('deleted_at');
                })
            ],
            'user_id' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'invoice_date.required' => __('custom_field_is_required', ['name' => __('invoice_date')]),
            'invoice_number.required' => __('custom_field_is_required', ['name' => __('invoice_number')]),
            'user_id.required' => __('custom_field_is_required', ['name' => __('customer')])
        ];
    }
}
