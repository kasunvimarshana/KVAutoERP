<?php

declare(strict_types=1);

namespace App\Http\Requests\Organization;

use App\Domain\Organization\Entities\Organization;
use Illuminate\Foundation\Http\FormRequest;

class CreateOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenant_id'   => ['required', 'uuid', 'exists:tenants,id'],
            'name'        => ['required', 'string', 'max:255'],
            'slug'        => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9-]+$/'],
            'parent_id'   => ['nullable', 'uuid', 'exists:organizations,id'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status'      => ['nullable', 'string', 'in:' . implode(',', [
                Organization::STATUS_ACTIVE,
                Organization::STATUS_INACTIVE,
            ])],
            'settings'    => ['nullable', 'array'],
            'metadata'    => ['nullable', 'array'],
        ];
    }
}
