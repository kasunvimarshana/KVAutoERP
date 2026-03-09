<?php

declare(strict_types=1);

namespace App\Application\Auth\Commands;

/**
 * Command: Log Out.
 *
 * Carries the data required to revoke a user's tokens.
 */
final readonly class LogoutCommand
{
    /**
     * @param  string  $userId     The authenticated user's UUID.
     * @param  string  $tenantId   Tenant scope.
     * @param  bool    $allDevices When true, revoke tokens on all devices.
     */
    public function __construct(
        public string $userId,
        public string $tenantId,
        public bool $allDevices = false,
    ) {}
}
