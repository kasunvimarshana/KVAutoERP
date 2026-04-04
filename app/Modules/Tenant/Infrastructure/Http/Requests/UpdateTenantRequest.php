<?php

declare(strict_types=1);

namespace Modules\Tenant\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tenantId = $this->route('id');

        return [
            'name'     => ['sometimes', 'string', 'max:255'],
            'slug'     => ['sometimes', 'string', 'max:100', 'alpha_dash', "unique:tenants,slug,{$tenantId}"],
            'status'   => ['sometimes', 'string', 'in:active,inactive,suspended'],
            'plan'     => ['sometimes', 'string', 'in:free,starter,professional,enterprise'],
            'settings' => ['sometimes', 'nullable', 'array'],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
