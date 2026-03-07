<?php

namespace App\Modules\Product\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by Keycloak middleware
    }

    public function rules(): array
    {
        return [
            'name'             => ['required', 'string', 'max:255'],
            'sku'              => ['required', 'string', 'max:100', 'unique:products,sku'],
            'description'      => ['required', 'string'],
            'price'            => ['required', 'numeric', 'min:0'],
            'category'         => ['required', 'string', 'max:100'],
            'status'           => ['sometimes', 'string', 'in:active,inactive,draft'],
            'weight'           => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'dimensions'       => ['sometimes', 'nullable', 'array'],
            'dimensions.width' => ['sometimes', 'numeric', 'min:0'],
            'dimensions.height'=> ['sometimes', 'numeric', 'min:0'],
            'dimensions.depth' => ['sometimes', 'numeric', 'min:0'],
            'metadata'         => ['sometimes', 'nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'Product name is required.',
            'sku.required'      => 'SKU is required.',
            'sku.unique'        => 'This SKU is already in use.',
            'description.required' => 'Product description is required.',
            'price.required'    => 'Product price is required.',
            'price.numeric'     => 'Price must be a valid number.',
            'price.min'         => 'Price cannot be negative.',
            'category.required' => 'Product category is required.',
            'status.in'         => 'Status must be one of: active, inactive, draft.',
        ];
    }
}
