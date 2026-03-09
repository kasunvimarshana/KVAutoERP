<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation for partial product updates (PATCH semantics — all fields optional).
 */
class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'             => ['sometimes', 'string', 'max:255'],
            'description'      => ['sometimes', 'nullable', 'string', 'max:5000'],
            'category_id'      => ['sometimes', 'nullable', 'uuid'],
            'price'            => ['sometimes', 'numeric', 'min:0'],
            'cost_price'       => ['sometimes', 'numeric', 'min:0'],
            'currency'         => ['sometimes', 'string', 'size:3'],
            'min_stock_level'  => ['sometimes', 'integer', 'min:0'],
            'max_stock_level'  => ['sometimes', 'integer', 'min:0'],
            'unit'             => ['sometimes', 'string', 'max:50'],
            'barcode'          => ['sometimes', 'nullable', 'string', 'max:100'],
            'status'           => ['sometimes', 'string', 'in:active,inactive,discontinued,draft'],
            'is_active'        => ['sometimes', 'boolean'],
            'tags'             => ['sometimes', 'array'],
            'tags.*'           => ['string', 'max:100'],
            'attributes'       => ['sometimes', 'array'],
        ];
    }
}
