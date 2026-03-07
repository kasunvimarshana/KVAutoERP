<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'            => ['required', 'string', 'max:255'],
            'sku'             => ['required', 'string', 'max:100'],
            'description'     => ['sometimes', 'nullable', 'string'],
            'category'        => ['sometimes', 'nullable', 'string', 'max:100'],
            'price'           => ['required', 'numeric', 'min:0'],
            'cost'            => ['required', 'numeric', 'min:0'],
            'stock_quantity'  => ['required', 'integer', 'min:0'],
            'min_stock_level' => ['required', 'integer', 'min:0'],
            'unit'            => ['sometimes', 'nullable', 'string', 'max:50'],
            'status'          => ['sometimes', 'in:active,inactive,discontinued'],
            'metadata'        => ['sometimes', 'nullable', 'array'],
        ];
    }
}
