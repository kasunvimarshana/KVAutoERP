<?php

declare(strict_types=1);

namespace App\Application\Auth\Commands;

/**
 * Command DTO for logging out a user.
 */
final readonly class LogoutCommand
{
    public function __construct(
        public string $userId,
        public string $tenantId,
        /** Revoke all tokens for this user, not just the current one. */
        public bool $revokeAll = false,
    ) {}
}
