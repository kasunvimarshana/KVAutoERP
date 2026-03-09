<?php

declare(strict_types=1);

namespace App\Application\Auth\DTOs;

/**
 * Data Transfer Object for authentication token responses.
 */
final readonly class TokenDTO
{
    public function __construct(
        public string $accessToken,
        public string $tokenType,
        public int $expiresIn,
        public UserDTO $user,
        public ?string $refreshToken = null,
    ) {}

    public function toArray(): array
    {
        return [
            'access_token'  => $this->accessToken,
            'token_type'    => $this->tokenType,
            'expires_in'    => $this->expiresIn,
            'refresh_token' => $this->refreshToken,
            'user'          => $this->user->toArray(),
        ];
    }
}
