<?php

declare(strict_types=1);

namespace App\Application\Auth\Commands;

/**
 * Command DTO for refreshing an access token.
 */
final readonly class RefreshTokenCommand
{
    public function __construct(
        public string $refreshToken,
        public string $tenantId,
    ) {}
}
