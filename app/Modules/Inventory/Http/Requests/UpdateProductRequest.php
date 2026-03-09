<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string,mixed> */
    public function rules(): array
    {
        $productId = $this->route('id') ?? $this->route('product');

        return [
            'name'        => ['sometimes', 'string', 'max:255'],
            'sku'         => ['sometimes', 'string', 'max:100', "unique:products,sku,{$productId}"],
            'description' => ['nullable', 'string'],
            'price'       => ['sometimes', 'numeric', 'min:0'],
            'quantity'    => ['sometimes', 'integer', 'min:0'],
            'status'      => ['sometimes', 'in:active,inactive,discontinued'],
            'category'    => ['nullable', 'string', 'max:100'],
            'metadata'    => ['nullable', 'array'],
        ];
    }
}
