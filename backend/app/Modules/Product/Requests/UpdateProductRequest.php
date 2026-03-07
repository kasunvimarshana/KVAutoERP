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
            'name'        => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'category'    => ['sometimes', 'nullable', 'string', 'max:100'],
            'brand'       => ['sometimes', 'nullable', 'string', 'max:100'],
            'unit'        => ['sometimes', 'string', 'max:50'],
            'price'       => ['sometimes', 'numeric', 'min:0'],
            'cost'        => ['sometimes', 'numeric', 'min:0'],
            'is_active'   => ['sometimes', 'boolean'],
            'attributes'  => ['sometimes', 'nullable', 'array'],
        ];
    }
}
