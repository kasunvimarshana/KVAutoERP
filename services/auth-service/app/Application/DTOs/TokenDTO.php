<?php

declare(strict_types=1);

namespace App\Application\DTOs;

/**
 * Token Data Transfer Object
 * Represents an authentication token response.
 */
final class TokenDTO
{
    public function __construct(
        public readonly string $accessToken,
        public readonly string $tokenType,
        public readonly int $expiresIn,
        public readonly ?string $refreshToken = null,
        public readonly array $scopes = [],
        public readonly array $user = [],
    ) {}

    public function toArray(): array
    {
        return [
            'access_token' => $this->accessToken,
            'token_type' => $this->tokenType,
            'expires_in' => $this->expiresIn,
            'refresh_token' => $this->refreshToken,
            'scopes' => $this->scopes,
            'user' => $this->user,
        ];
    }
}
