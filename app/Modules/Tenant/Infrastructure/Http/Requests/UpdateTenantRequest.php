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
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', 'unique:tenants,slug,' . $this->route('id')],
            'plan' => ['sometimes', 'string', 'in:starter,professional,enterprise'],
            'locale' => ['sometimes', 'string', 'max:10'],
            'timezone' => ['sometimes', 'string', 'max:50'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'status' => ['sometimes', 'string', 'in:active,suspended,cancelled'],
        ];
    }
}
