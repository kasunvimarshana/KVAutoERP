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
            'user_id'                      => ['nullable', 'uuid'],
            'items'                        => ['required', 'array', 'min:1'],
            'items.*.product_id'           => ['required', 'uuid'],
            'items.*.quantity'             => ['required', 'integer', 'min:1'],
            'items.*.unit_price'           => ['nullable', 'numeric', 'min:0'],
            'items.*.discount'             => ['nullable', 'numeric', 'min:0'],
            'notes'                        => ['nullable', 'string', 'max:1000'],
            'shipping_address'             => ['nullable', 'array'],
            'shipping_address.street'      => ['nullable', 'string'],
            'shipping_address.city'        => ['nullable', 'string'],
            'shipping_address.country'     => ['nullable', 'string'],
            'billing_address'              => ['nullable', 'array'],
            'currency'                     => ['nullable', 'string', 'size:3'],
            'discount'                     => ['nullable', 'numeric', 'min:0'],
            'tax'                          => ['nullable', 'numeric', 'min:0'],
            'metadata'                     => ['nullable', 'array'],
        ];
    }
}
