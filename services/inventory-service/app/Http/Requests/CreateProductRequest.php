<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation for creating a new product.
 */
class CreateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sku'              => ['required', 'string', 'max:50', 'regex:/^[A-Z0-9][A-Z0-9\-]{1,49}$/i'],
            'name'             => ['required', 'string', 'max:255'],
            'description'      => ['nullable', 'string', 'max:5000'],
            'category_id'      => ['nullable', 'uuid'],
            'price'            => ['required', 'numeric', 'min:0'],
            'cost_price'       => ['nullable', 'numeric', 'min:0'],
            'currency'         => ['nullable', 'string', 'size:3'],
            'stock_quantity'   => ['required', 'integer', 'min:0'],
            'min_stock_level'  => ['nullable', 'integer', 'min:0'],
            'max_stock_level'  => ['nullable', 'integer', 'min:0'],
            'unit'             => ['nullable', 'string', 'max:50'],
            'barcode'          => ['nullable', 'string', 'max:100'],
            'status'           => ['nullable', 'string', 'in:active,inactive,discontinued,draft'],
            'tags'             => ['nullable', 'array'],
            'tags.*'           => ['string', 'max:100'],
            'attributes'       => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'sku.regex' => 'SKU must contain only uppercase letters, numbers, and hyphens.',
        ];
    }
}
