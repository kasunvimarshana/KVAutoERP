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
            'sku'         => ['required', 'string', 'max:100'],
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category'    => ['nullable', 'string', 'max:100'],
            'brand'       => ['nullable', 'string', 'max:100'],
            'unit'        => ['nullable', 'string', 'max:50'],
            'price'       => ['nullable', 'numeric', 'min:0'],
            'cost'        => ['nullable', 'numeric', 'min:0'],
            'is_active'   => ['nullable', 'boolean'],
            'attributes'  => ['nullable', 'array'],
        ];
    }
}
