<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'name'              => ['sometimes', 'string', 'max:255'],
            'sku'               => ['sometimes', 'string', 'max:100'],
            'barcode'           => ['sometimes', 'nullable', 'string', 'max:255'],
            'type'              => ['sometimes', 'string', 'in:physical,service,digital,combo,variable'],
            'status'            => ['sometimes', 'string', 'in:active,inactive,discontinued'],
            'category_id'       => ['sometimes', 'nullable', 'integer', 'exists:product_categories,id'],
            'description'       => ['sometimes', 'nullable', 'string'],
            'short_description' => ['sometimes', 'nullable', 'string'],
            'weight'            => ['sometimes', 'nullable', 'numeric'],
            'dimensions'        => ['sometimes', 'nullable', 'array'],
            'images'            => ['sometimes', 'nullable', 'array'],
            'tags'              => ['sometimes', 'nullable', 'array'],
            'is_taxable'        => ['sometimes', 'boolean'],
            'tax_class'         => ['sometimes', 'nullable', 'string'],
            'has_serial'        => ['sometimes', 'boolean'],
            'has_batch'         => ['sometimes', 'boolean'],
            'has_lot'           => ['sometimes', 'boolean'],
            'is_serialized'     => ['sometimes', 'boolean'],
        ];
    }
}
