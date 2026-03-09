<?php

declare(strict_types=1);

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Create Inventory Item Request.
 */
class CreateInventoryItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'sku'              => ['required', 'string', 'max:100'],
            'name'             => ['required', 'string', 'max:255'],
            'description'      => ['sometimes', 'nullable', 'string'],
            'category_id'      => ['sometimes', 'nullable', 'string', 'uuid'],
            'warehouse_id'     => ['sometimes', 'nullable', 'string', 'uuid'],
            'quantity'         => ['required', 'integer', 'min:0'],
            'reorder_point'    => ['sometimes', 'integer', 'min:0'],
            'reorder_quantity' => ['sometimes', 'integer', 'min:1'],
            'unit_cost'        => ['sometimes', 'numeric', 'min:0'],
            'unit_price'       => ['sometimes', 'numeric', 'min:0'],
            'unit_of_measure'  => ['sometimes', 'string', 'max:50'],
            'status'           => ['sometimes', 'string', 'in:active,inactive,discontinued'],
            'metadata'         => ['sometimes', 'array'],
            'tags'             => ['sometimes', 'array'],
            'tags.*'           => ['string', 'max:50'],
        ];
    }
}
