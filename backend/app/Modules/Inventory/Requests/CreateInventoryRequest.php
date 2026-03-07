<?php

namespace App\Modules\Inventory\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id'         => ['required', 'uuid'],
            'quantity'           => ['nullable', 'integer', 'min:0'],
            'reserved_quantity'  => ['nullable', 'integer', 'min:0'],
            'minimum_quantity'   => ['nullable', 'integer', 'min:0'],
            'maximum_quantity'   => ['nullable', 'integer', 'min:0'],
            'warehouse_location' => ['nullable', 'string', 'max:255'],
            'status'             => ['nullable', 'string', 'in:in_stock,low_stock,out_of_stock'],
            'metadata'           => ['nullable', 'array'],
        ];
    }
}
