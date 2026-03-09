<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Shared\Base\BaseRequest;

/**
 * Refresh Token Form Request.
 *
 * Validates the payload for POST /auth/refresh.
 */
final class RefreshTokenRequest extends BaseRequest
{
    /**
     * @return array<string, array<string>|string>
     */
    public function rules(): array
    {
        return [
            'refresh_token' => ['required', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'refresh_token.required' => 'A refresh token is required.',
            'refresh_token.string'   => 'The refresh token must be a string.',
        ];
    }
}
