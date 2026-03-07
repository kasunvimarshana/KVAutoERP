<?php

namespace App\Http\Requests;

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
            'sku'             => 'required|string|max:100',
            'name'            => 'required|string|max:255',
            'quantity'        => 'required|integer|min:0',
            'unit_cost'       => 'required|numeric|min:0',
            'unit_price'      => 'required|numeric|min:0',
            'description'     => 'nullable|string|max:2000',
            'category'        => 'nullable|string|max:100',
            'location'        => 'nullable|string|max:255',
            'min_stock_level' => 'nullable|integer|min:0',
            'max_stock_level' => 'nullable|integer|min:0',
            'metadata'        => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'sku.required'        => 'A SKU is required.',
            'name.required'       => 'An item name is required.',
            'quantity.required'   => 'An initial quantity is required.',
            'unit_cost.required'  => 'Unit cost is required.',
            'unit_price.required' => 'Unit price is required.',
        ];
    }
}
