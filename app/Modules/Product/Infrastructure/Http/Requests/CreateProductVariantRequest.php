<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateProductVariantRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'tenant_id'        => ['required', 'integer'],
            'product_id'       => ['required', 'integer', 'exists:products,id'],
            'name'             => ['required', 'string', 'max:255'],
            'sku'              => ['required', 'string', 'max:100', 'unique:product_variants,sku'],
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
