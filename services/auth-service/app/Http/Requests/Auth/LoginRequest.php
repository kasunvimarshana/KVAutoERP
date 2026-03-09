<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'email'                   => ['required', 'string', 'email:rfc'],
            'password'                => ['required', 'string'],
            'remember'                => ['nullable', 'boolean'],
            'device_info'             => ['nullable', 'array'],
            'device_info.user_agent'  => ['nullable', 'string', 'max:512'],
            'device_info.device_id'   => ['nullable', 'string', 'max:255'],
            'device_info.platform'    => ['nullable', 'string', 'in:ios,android,web,desktop'],
        ];
    }
}
