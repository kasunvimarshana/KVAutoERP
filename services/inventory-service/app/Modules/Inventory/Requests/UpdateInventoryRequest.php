<?php

namespace App\Modules\Inventory\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id'         => ['sometimes', 'required', 'integer', 'min:1'],
            'product_sku'        => ['sometimes', 'required', 'string', 'max:100'],
            'quantity'           => ['sometimes', 'required', 'integer', 'min:0'],
            'reserved_quantity'  => ['sometimes', 'integer', 'min:0'],
            'warehouse_location' => ['sometimes', 'nullable', 'string', 'max:100'],
            'reorder_level'      => ['sometimes', 'integer', 'min:0'],
            'reorder_quantity'   => ['sometimes', 'integer', 'min:1'],
            'unit_cost'          => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'notes'              => ['sometimes', 'nullable', 'string'],
        ];
    }
}
