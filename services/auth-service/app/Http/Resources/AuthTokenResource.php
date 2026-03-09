<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Auth Token API Resource.
 *
 * Wraps the token payload returned after successful login or token refresh,
 * embedding a {@see UserResource} for the authenticated user's profile.
 */
final class AuthTokenResource extends JsonResource
{
    /**
     * @param  array{access_token: string, refresh_token: string|null, token_type: string, expires_in: int, user: array}  $resource
     */
    public function __construct(mixed $resource)
    {
        parent::__construct($resource);
    }

    /**
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = is_array($this->resource) ? $this->resource : (array) $this->resource;

        return [
            'access_token'  => $data['access_token'] ?? null,
            'refresh_token' => $data['refresh_token'] ?? null,
            'token_type'    => $data['token_type'] ?? 'Bearer',
            'expires_in'    => $data['expires_in'] ?? 3600,
            'user'          => isset($data['user'])
                ? new UserResource($data['user'])
                : null,
        ];
    }
}
