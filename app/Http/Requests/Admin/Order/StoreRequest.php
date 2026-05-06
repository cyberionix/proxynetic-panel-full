<?php

namespace App\Http\Requests\Admin\Order;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'user_id' => 'required',
            'product_id' => 'required',
            'start_date' => 'required|date_format:' . defaultDateFormat(),
            'end_date' => 'required|date_format:' . defaultDateFormat(),
            'price_id' => 'required',
            'quantity' => 'required|min:1|integer',
            'auto_delivery' => 'boolean',
        ];
    }

    public function messages()
    {
        return [
            'user_id.required' => __('custom_field_is_required', ['name' => __('customer')]),
            'product_id.required' => __('custom_field_is_required', ['name' => __('product')]),
            'start_date.required' => __('custom_field_is_required', ['name' => __('start_date')]),
            'end_date.required' => __('custom_field_is_required', ['name' => __('end_date')]),
            'price_id.required' => __('custom_field_is_required', ['name' => __('price')]),
            'quantity.required' => __('custom_field_is_required', ['name' => __('quantity')])
        ];
    }
}
