<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateProductCategoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'tenant_id'   => ['required', 'integer'],
            'name'        => ['required', 'string', 'max:255'],
            'slug'        => ['required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'parent_id'   => ['sometimes', 'nullable', 'integer', 'exists:product_categories,id'],
            'image'       => ['sometimes', 'nullable', 'string'],
            'is_active'   => ['sometimes', 'boolean'],
            'sort_order'  => ['sometimes', 'integer'],
            'metadata'    => ['sometimes', 'nullable', 'array'],
        ];
    }
}
