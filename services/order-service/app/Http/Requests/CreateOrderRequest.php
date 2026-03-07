<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'items'                  => ['required', 'array', 'min:1'],
            'items.*.product_id'     => ['required', 'uuid'],
            'items.*.quantity'       => ['required', 'integer', 'min:1'],
            'items.*.unit_price'     => ['sometimes', 'numeric', 'min:0'],
            'tax'                    => ['sometimes', 'numeric', 'min:0'],
            'discount'               => ['sometimes', 'numeric', 'min:0'],
            'currency'               => ['sometimes', 'string', 'size:3'],
            'shipping_address'       => ['required', 'array'],
            'shipping_address.line1' => ['required', 'string'],
            'shipping_address.city'  => ['required', 'string'],
            'shipping_address.country' => ['required', 'string'],
            'billing_address'        => ['sometimes', 'array'],
            'notes'                  => ['sometimes', 'nullable', 'string'],
        ];
    }
}
