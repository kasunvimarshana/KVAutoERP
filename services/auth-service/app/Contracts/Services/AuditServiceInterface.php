<?php

declare(strict_types=1);

namespace App\Contracts\Services;

interface AuditServiceInterface
{
    /**
     * Log an authentication event (login, logout, refresh, password change, etc.).
     */
    public function log(
        string $event,
        string $userId,
        string $tenantId,
        array $metadata = [],
        string $ipAddress = '',
        string $userAgent = '',
    ): void;

    /**
     * Record a failed login attempt.
     */
    public function logFailedLogin(string $email, string $tenantId, string $ipAddress, string $userAgent, string $reason): void;

    /**
     * Record a suspicious activity alert and trigger notifications.
     */
    public function logSuspiciousActivity(string $userId, string $tenantId, string $activityType, array $context): void;

    /**
     * Check whether the user/IP has exceeded the failed-login threshold.
     */
    public function isSuspiciousActivity(string $userId, string $ipAddress): bool;
}
