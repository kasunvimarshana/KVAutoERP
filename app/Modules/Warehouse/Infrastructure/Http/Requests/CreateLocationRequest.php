<?php

declare(strict_types=1);

namespace Modules\Warehouse\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenant_id'    => ['required', 'integer'],
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'parent_id'    => ['sometimes', 'nullable', 'integer', 'exists:warehouse_locations,id'],
            'name'         => ['required', 'string', 'max:255'],
            'code'         => ['required', 'string', 'max:50'],
            'type'         => ['sometimes', 'string', 'in:zone,aisle,rack,shelf,bin'],
            'barcode'      => ['sometimes', 'nullable', 'string', 'max:255'],
            'capacity'     => ['sometimes', 'nullable', 'numeric'],
            'is_active'    => ['sometimes', 'boolean'],
        ];
    }
}
