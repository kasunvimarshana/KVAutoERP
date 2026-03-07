<?php

namespace App\Modules\Product\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'sku' => 'sometimes|string|max:100',
            'price' => 'sometimes|numeric|min:0',
            'category' => 'sometimes|string|max:100',
            'attributes' => 'sometimes|array',
            'is_active' => 'sometimes|boolean',
        ];
    }
}
