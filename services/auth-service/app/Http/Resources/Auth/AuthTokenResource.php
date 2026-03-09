<?php

declare(strict_types=1);

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Auth Token Resource.
 *
 * Formats the authentication response returned after successful login.
 */
class AuthTokenResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'success'      => true,
            'access_token' => $this->resource['access_token'],
            'token_type'   => $this->resource['token_type'] ?? 'Bearer',
            'expires_at'   => $this->resource['expires_at'] ?? null,
            'scopes'       => $this->resource['scopes'] ?? [],
            'user'         => new UserResource($this->resource['user']),
        ];
    }
}
