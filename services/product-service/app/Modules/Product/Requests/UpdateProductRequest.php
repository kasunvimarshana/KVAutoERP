<?php

namespace App\Modules\Product\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by Keycloak middleware
    }

    public function rules(): array
    {
        $productId = $this->route('id');

        return [
            'name'             => ['sometimes', 'required', 'string', 'max:255'],
            'sku'              => ['sometimes', 'required', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($productId)],
            'description'      => ['sometimes', 'required', 'string'],
            'price'            => ['sometimes', 'required', 'numeric', 'min:0'],
            'category'         => ['sometimes', 'required', 'string', 'max:100'],
            'status'           => ['sometimes', 'string', 'in:active,inactive,draft'],
            'weight'           => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'dimensions'       => ['sometimes', 'nullable', 'array'],
            'dimensions.width' => ['sometimes', 'numeric', 'min:0'],
            'dimensions.height'=> ['sometimes', 'numeric', 'min:0'],
            'dimensions.depth' => ['sometimes', 'numeric', 'min:0'],
            'metadata'         => ['sometimes', 'nullable', 'array'],
        ];
    }
}
