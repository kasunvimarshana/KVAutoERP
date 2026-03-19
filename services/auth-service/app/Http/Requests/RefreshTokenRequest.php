<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates refresh token requests.
 */
final class RefreshTokenRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'refresh_token' => ['required', 'string'],
            'device_id'     => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'refresh_token.required' => 'A refresh token is required.',
            'device_id.required'     => 'A device identifier is required.',
        ];
    }
}
