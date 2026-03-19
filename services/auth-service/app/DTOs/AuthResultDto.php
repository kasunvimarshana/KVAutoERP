<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class AuthResultDto
{
    public function __construct(
        public string $accessToken,
        public string $refreshToken,
        public int    $expiresIn,
        public string $tokenType = 'Bearer',
        public array  $claims    = [],
    ) {}

    public function toArray(): array
    {
        return [
            'access_token'  => $this->accessToken,
            'refresh_token' => $this->refreshToken,
            'expires_in'    => $this->expiresIn,
            'token_type'    => $this->tokenType,
        ];
    }
}
