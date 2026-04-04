<?php

declare(strict_types=1);

namespace Modules\Configuration\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrgUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenant_id'   => ['required', 'integer'],
            'name'        => ['required', 'string', 'max:255'],
            'code'        => ['required', 'string', 'max:50'],
            'type'        => ['required', 'string', 'max:100'],
            'parent_id'   => ['sometimes', 'nullable', 'integer', 'exists:org_units,id'],
            'description' => ['sometimes', 'nullable', 'string'],
            'is_active'   => ['sometimes', 'boolean'],
            'metadata'    => ['sometimes', 'nullable', 'array'],
        ];
    }
}
