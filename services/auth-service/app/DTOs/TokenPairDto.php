<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class TokenPairDto
{
    public function __construct(
        public string $accessToken,
        public string $refreshToken,
        public int $accessTokenExpiresIn,
        public int $refreshTokenExpiresIn,
        public string $tokenType = 'Bearer',
    ) {}

    public function toArray(): array
    {
        return [
            'access_token'              => $this->accessToken,
            'refresh_token'             => $this->refreshToken,
            'token_type'                => $this->tokenType,
            'expires_in'                => $this->accessTokenExpiresIn,
            'refresh_token_expires_in'  => $this->refreshTokenExpiresIn,
        ];
    }
}
