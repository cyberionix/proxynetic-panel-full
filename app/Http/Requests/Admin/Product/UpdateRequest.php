<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'product.name' => 'required',
            'product.delivery_type' => 'required',
            'product.category_id' => 'required',
            'isp_image' => 'nullable|file|mimes:jpeg,jpg,png|max:20480',
        ];
    }

    public function messages()
    {
        return [
            'product.name.required' => __('custom_field_is_required', ['name' => __('product_name')]),
            'isp_image.file' => "Lütfen dosya yükleyin.",
            'isp_image.mimes' => "Sadece .jpeg, .jpg ve .png uzantılı dosyalar kabul edilir.",
            'isp_image.max' => "Dosya boyutu en fazla 20MB olmalıdır.",
            'product.delivery_type.required' => __('custom_field_is_required', ['name' => __('delivery_type')]),
            'product.category_id.required' => __('custom_field_is_required', ['name' => __(":name_selection", ["name" => __("category")])]),
            'delivery_count.required' => __('custom_field_is_required', ['name' => __('piece')]),
            'product.delivery_items.required' => __('custom_field_is_required', ['name' => "Proxy Listesi"]),
            'data_size.required' => __('custom_field_is_required', ['name' => __('usage_limit')]),
            'data_size_type.required' => __('custom_field_is_required', ['name' => "Kullanım limit tipi (MB, GB)"]),
//            'product.auth_tokens.required' => __('custom_field_is_required', ['name' => "Auth Token"]),
        ];
    }

    public function withValidator($validator)
    {
        $validator->sometimes(['delivery_count', 'product.delivery_items'], 'required', function ($input) {
            return $input->product['delivery_type'] === 'STACK';
        });

        $validator->sometimes(['data_size', 'data_size_type'], 'required', function ($input) {
            return in_array($input->product['delivery_type'] ?? '', ['LOCALTONET', 'LOCALTONETV4'], true);
        });

        $validator->sometimes(['product.token_pool_id'], 'required', function ($input) {
            return ($input->product['delivery_type'] ?? '') === 'LOCALTONET';
        });
    }
}
