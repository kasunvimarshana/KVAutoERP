<?php

declare(strict_types=1);

namespace App\Contracts;

interface SuspiciousActivityServiceContract
{
    /**
     * Record a failed login attempt for the given identifier (email or IP).
     * Returns true if the account/IP is now blocked after too many failures.
     */
    public function recordFailedAttempt(string $identifier, string $ipAddress): bool;

    /**
     * Reset the failed-attempt counter on a successful login.
     */
    public function resetFailedAttempts(string $identifier): void;

    /**
     * Return true if the identifier (email or IP) is currently blocked.
     */
    public function isBlocked(string $identifier): bool;

    /**
     * Manually block an identifier (e.g. after admin review).
     */
    public function block(string $identifier, int $ttl = 3600): void;

    /**
     * Unblock an identifier.
     */
    public function unblock(string $identifier): void;

    /**
     * Return the number of remaining failed attempts before the identifier is blocked.
     */
    public function remainingAttempts(string $identifier): int;
}
