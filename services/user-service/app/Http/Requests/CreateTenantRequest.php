<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:255'],
            'slug'          => ['sometimes', 'string', 'max:100', 'unique:tenants,slug'],
            'iam_provider'  => ['sometimes', 'string', 'in:local,okta,keycloak,azure_ad,oauth2'],
            'configuration' => ['sometimes', 'array'],
        ];
    }
}
