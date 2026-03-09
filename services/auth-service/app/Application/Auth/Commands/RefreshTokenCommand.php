<?php

declare(strict_types=1);

namespace App\Application\Auth\Commands;

/**
 * Command: Refresh Token.
 *
 * Carries the opaque refresh token string used to obtain a new access token.
 */
final readonly class RefreshTokenCommand
{
    /**
     * @param  string  $refreshToken  The opaque refresh token issued at login.
     * @param  string  $tenantId      Tenant scope for the refresh operation.
     */
    public function __construct(
        public string $refreshToken,
        public string $tenantId,
    ) {}
}
