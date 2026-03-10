<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTenantRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'   => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'in:active,inactive,suspended'],
            'plan'   => ['nullable', 'string', 'in:free,starter,pro,enterprise'],
            'config' => ['nullable', 'array'],
        ];
    }
}
