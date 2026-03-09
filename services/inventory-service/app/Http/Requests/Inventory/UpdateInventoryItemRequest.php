<?php

declare(strict_types=1);

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Update Inventory Item Request.
 */
class UpdateInventoryItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name'             => ['sometimes', 'string', 'max:255'],
            'description'      => ['sometimes', 'nullable', 'string'],
            'category_id'      => ['sometimes', 'nullable', 'string', 'uuid'],
            'warehouse_id'     => ['sometimes', 'nullable', 'string', 'uuid'],
            'quantity'         => ['sometimes', 'integer', 'min:0'],
            'reorder_point'    => ['sometimes', 'integer', 'min:0'],
            'reorder_quantity' => ['sometimes', 'integer', 'min:1'],
            'unit_cost'        => ['sometimes', 'numeric', 'min:0'],
            'unit_price'       => ['sometimes', 'numeric', 'min:0'],
            'unit_of_measure'  => ['sometimes', 'string', 'max:50'],
            'status'           => ['sometimes', 'string', 'in:active,inactive,discontinued'],
            'metadata'         => ['sometimes', 'array'],
            'tags'             => ['sometimes', 'array'],
        ];
    }
}
