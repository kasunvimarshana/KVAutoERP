<?php

declare(strict_types=1);

namespace KvSaas\Contracts\Auth\Dto;

/** Token pair issued on login or refresh. */
final readonly class TokenPairDto
{
    public function __construct(
        public string $accessToken,
        public string $refreshToken,
        public int    $expiresIn,
        public string $tokenType = 'Bearer',
    ) {}

    /** @return array<string, mixed> */
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
