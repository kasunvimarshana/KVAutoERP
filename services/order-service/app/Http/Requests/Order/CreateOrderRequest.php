<?php

declare(strict_types=1);

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Create Order Request.
 */
class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'customer_id'                         => ['required', 'string'],
            'items'                               => ['required', 'array', 'min:1'],
            'items.*.inventory_item_id'           => ['required', 'string', 'uuid'],
            'items.*.sku'                         => ['required', 'string'],
            'items.*.name'                        => ['required', 'string'],
            'items.*.quantity'                    => ['required', 'integer', 'min:1'],
            'items.*.unit_price'                  => ['required', 'numeric', 'min:0'],
            'tax_amount'                          => ['sometimes', 'numeric', 'min:0'],
            'currency'                            => ['sometimes', 'string', 'size:3'],
            'notes'                               => ['sometimes', 'nullable', 'string'],
            'payment_method'                      => ['required', 'array'],
            'payment_method.type'                 => ['required', 'string', 'in:credit_card,debit_card,bank_transfer,wallet'],
            'payment_method.token'                => ['required', 'string'],
        ];
    }
}
