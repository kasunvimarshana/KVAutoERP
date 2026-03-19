<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'        => ['required_without:code', 'email', 'max:255'],
            'password'     => ['required_if:provider,local'],
            'provider'     => ['sometimes', 'string', 'in:local,okta,keycloak,azure_ad,oauth2'],
            'tenant_id'    => ['sometimes', 'string', 'max:255'],
            'code'         => ['required_if:provider,okta,keycloak,azure_ad,oauth2'],
            'redirect_uri' => ['sometimes', 'url'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required_without' => 'Email is required for local authentication.',
            'password.required_if'   => 'Password is required for local authentication.',
            'code.required_if'       => 'Authorization code is required for federated authentication.',
        ];
    }
}
