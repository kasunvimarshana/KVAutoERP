<?php

namespace App\Modules\Order\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id'                         => ['required', 'integer', 'min:1'],
            'items'                           => ['required', 'array', 'min:1'],
            'items.*.product_id'              => ['required', 'integer', 'min:1'],
            'items.*.product_sku'             => ['required', 'string'],
            'items.*.product_name'            => ['required', 'string'],
            'items.*.quantity'                => ['required', 'integer', 'min:1'],
            'items.*.unit_price'              => ['required', 'numeric', 'min:0'],
            'shipping_address'                => ['required', 'array'],
            'shipping_address.street'         => ['required', 'string'],
            'shipping_address.city'           => ['required', 'string'],
            'shipping_address.country'        => ['required', 'string', 'size:2'],
            'shipping_address.postal_code'    => ['required', 'string'],
            'billing_address'                 => ['sometimes', 'array'],
            'currency'                        => ['sometimes', 'string', 'size:3'],
            'notes'                           => ['sometimes', 'nullable', 'string'],
        ];
    }
}
