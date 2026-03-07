<?php

namespace App\Http\Requests;

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
            'customer_id'                  => ['required', 'string', 'max:255'],
            'customer_name'                => ['required', 'string', 'max:255'],
            'customer_email'               => ['required', 'email', 'max:255'],
            'payment_method'               => ['required', 'string', 'in:credit_card,debit_card,bank_transfer,paypal,cash'],
            'payment_token'                => ['nullable', 'string'],

            'items'                        => ['required', 'array', 'min:1'],
            'items.*.inventory_id'         => ['required', 'string'],
            'items.*.product_name'         => ['required', 'string', 'max:255'],
            'items.*.quantity'             => ['required', 'integer', 'min:1'],
            'items.*.unit_price'           => ['required', 'numeric', 'min:0'],
            'items.*.sku'                  => ['nullable', 'string', 'max:100'],

            'shipping_address'             => ['nullable', 'array'],
            'shipping_address.line1'       => ['required_with:shipping_address', 'string'],
            'shipping_address.city'        => ['required_with:shipping_address', 'string'],
            'shipping_address.country'     => ['required_with:shipping_address', 'string', 'size:2'],

            'billing_address'              => ['nullable', 'array'],
            'billing_address.line1'        => ['required_with:billing_address', 'string'],
            'billing_address.city'         => ['required_with:billing_address', 'string'],
            'billing_address.country'      => ['required_with:billing_address', 'string', 'size:2'],

            'discount'                     => ['nullable', 'numeric', 'min:0'],
            'tax'                          => ['nullable', 'numeric', 'min:0'],
            'notes'                        => ['nullable', 'string', 'max:1000'],
            'currency'                     => ['nullable', 'string', 'size:3'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required'                   => 'At least one order item is required.',
            'items.*.inventory_id.required'    => 'Each item must reference an inventory ID.',
            'items.*.quantity.min'             => 'Item quantity must be at least 1.',
            'items.*.unit_price.min'           => 'Item unit price cannot be negative.',
            'shipping_address.country.size'    => 'Country must be a 2-letter ISO code.',
        ];
    }

    /**
     * Compute total from items for the saga payload.
     */
    public function validated($key = null, $default = null): mixed
    {
        $data = parent::validated($key, $default);

        if (is_array($data)) {
            $subtotal       = collect($data['items'])->sum(fn ($i) => ($i['unit_price'] ?? 0) * ($i['quantity'] ?? 1));
            $data['total']  = $subtotal + ($data['tax'] ?? 0) - ($data['discount'] ?? 0);
        }

        return $data;
    }
}
