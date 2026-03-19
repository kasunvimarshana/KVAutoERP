<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'            => ['required', 'string', 'max:255'],
            'email'           => ['required', 'email', 'max:255'],
            'password'        => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
            'tenant_id'       => ['required', 'uuid', 'exists:tenants,id'],
            'device_id'       => ['required', 'string', 'max:255'],
            'device_name'     => ['nullable', 'string', 'max:100'],
            'organisation_id' => ['nullable', 'uuid', 'exists:organisations,id'],
            'branch_id'       => ['nullable', 'uuid', 'exists:branches,id'],
        ];
    }
}
