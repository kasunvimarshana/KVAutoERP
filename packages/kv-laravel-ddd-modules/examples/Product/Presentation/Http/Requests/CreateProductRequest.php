<?php

declare(strict_types=1);

namespace LaravelDDD\Examples\Product\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for creating a new product.
 */
class CreateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // TODO: Add real authorization logic (e.g. Gate check)
        return true;
    }

    /**
     * Get the validation rules for the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'min:1', 'max:255'],
            'price_in_cents' => ['required', 'integer', 'min:0'],
            'currency'      => ['sometimes', 'string', 'size:3'],
        ];
    }
}
