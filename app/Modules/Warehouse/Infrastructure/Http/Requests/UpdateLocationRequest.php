<?php

declare(strict_types=1);

namespace Modules\Warehouse\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'      => ['sometimes', 'string', 'max:255'],
            'code'      => ['sometimes', 'string', 'max:50'],
            'type'      => ['sometimes', 'string', 'in:zone,aisle,rack,shelf,bin'],
            'barcode'   => ['sometimes', 'nullable', 'string', 'max:255'],
            'capacity'  => ['sometimes', 'nullable', 'numeric'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
