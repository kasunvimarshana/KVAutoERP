<?php

declare(strict_types=1);

namespace Modules\Warehouse\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateWarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenant_id'       => ['required', 'integer'],
            'name'            => ['required', 'string', 'max:255'],
            'code'            => ['required', 'string', 'max:50'],
            'type'            => ['sometimes', 'string', 'in:standard,bonded,cold,virtual'],
            'address'         => ['sometimes', 'nullable', 'array'],
            'is_active'       => ['sometimes', 'boolean'],
            'manager_user_id' => ['sometimes', 'nullable', 'integer'],
        ];
    }
}
