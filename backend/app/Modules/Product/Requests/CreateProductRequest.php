<?php

namespace App\Modules\Product\Requests;

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
            'name' => 'required|string|max:255',
            'description' => 'sometimes|string',
            'sku' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'category' => 'sometimes|string|max:100',
            'tenant_id' => 'required|integer|exists:tenants,id',
            'attributes' => 'sometimes|array',
            'is_active' => 'sometimes|boolean',
        ];
    }
}
