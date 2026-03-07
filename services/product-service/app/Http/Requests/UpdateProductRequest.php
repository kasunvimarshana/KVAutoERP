<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'            => ['sometimes', 'string', 'max:255'],
            'sku'             => ['sometimes', 'string', 'max:100'],
            'description'     => ['sometimes', 'nullable', 'string'],
            'category'        => ['sometimes', 'nullable', 'string', 'max:100'],
            'price'           => ['sometimes', 'numeric', 'min:0'],
            'cost'            => ['sometimes', 'numeric', 'min:0'],
            'stock_quantity'  => ['sometimes', 'integer', 'min:0'],
            'min_stock_level' => ['sometimes', 'integer', 'min:0'],
            'unit'            => ['sometimes', 'nullable', 'string', 'max:50'],
            'status'          => ['sometimes', 'in:active,inactive,discontinued'],
            'metadata'        => ['sometimes', 'nullable', 'array'],
        ];
    }
}
