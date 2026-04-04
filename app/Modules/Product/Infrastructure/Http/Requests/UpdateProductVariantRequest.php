<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductVariantRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'name'             => ['sometimes', 'string', 'max:255'],
            'sku'              => ['sometimes', 'string', 'max:100'],
            'barcode'          => ['sometimes', 'nullable', 'string', 'max:255'],
            'attributes'       => ['sometimes', 'array'],
            'price'            => ['sometimes', 'nullable', 'numeric'],
            'cost'             => ['sometimes', 'nullable', 'numeric'],
            'weight'           => ['sometimes', 'nullable', 'numeric'],
            'is_active'        => ['sometimes', 'boolean'],
            'stock_management' => ['sometimes', 'boolean'],
        ];
    }
}
