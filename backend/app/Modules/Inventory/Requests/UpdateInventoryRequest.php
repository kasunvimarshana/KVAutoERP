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
            'quantity'           => ['sometimes', 'integer', 'min:0'],
            'reserved_quantity'  => ['sometimes', 'integer', 'min:0'],
            'minimum_quantity'   => ['sometimes', 'integer', 'min:0'],
            'maximum_quantity'   => ['sometimes', 'nullable', 'integer', 'min:0'],
            'warehouse_location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'status'             => ['sometimes', 'string', 'in:in_stock,low_stock,out_of_stock'],
            'metadata'           => ['sometimes', 'nullable', 'array'],
        ];
    }
}
