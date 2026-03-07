<?php

namespace App\Http\Requests;

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
            'sku'             => 'sometimes|string|max:100',
            'name'            => 'sometimes|string|max:255',
            'quantity'        => 'sometimes|integer|min:0',
            'unit_cost'       => 'sometimes|numeric|min:0',
            'unit_price'      => 'sometimes|numeric|min:0',
            'description'     => 'nullable|string|max:2000',
            'category'        => 'nullable|string|max:100',
            'location'        => 'nullable|string|max:255',
            'min_stock_level' => 'nullable|integer|min:0',
            'max_stock_level' => 'nullable|integer|min:0',
            'status'          => 'nullable|in:active,inactive,discontinued',
            'metadata'        => 'nullable|array',
        ];
    }
}
