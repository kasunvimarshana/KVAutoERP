<?php

declare(strict_types=1);

namespace App\Contracts\Services;

/**
 * Contract for the immutable, append-only authentication audit log.
 *
 * Every authentication event (login, logout, refresh, failed attempts,
 * suspicious activity) must be recorded via this service to satisfy
 * regulatory and compliance requirements.
 */
interface AuditLogServiceInterface
{
    /**
     * Record a successful login event.
     *
     * @param  string                $userId    User UUID.
     * @param  string                $tenantId  Tenant UUID.
     * @param  string                $deviceId  Device identifier.
     * @param  string                $ipAddress IP address.
     * @param  string                $userAgent User-agent string.
     * @param  array<string, mixed>  $metadata  Extra event data.
     * @return void
     */
    public function logLogin(
        string $userId,
        string $tenantId,
        string $deviceId,
        string $ipAddress,
        string $userAgent,
        array $metadata = [],
    ): void;

    /**
     * Record a failed login attempt.
     *
     * @param  string|null           $userId    User UUID (null when user not found).
     * @param  string                $tenantId  Tenant UUID.
     * @param  string                $email     Attempted email.
     * @param  string                $ipAddress IP address.
     * @param  string                $userAgent User-agent string.
     * @param  array<string, mixed>  $metadata  Extra event data.
     * @return void
     */
    public function logFailedLogin(
        ?string $userId,
        string $tenantId,
        string $email,
        string $ipAddress,
        string $userAgent,
        array $metadata = [],
    ): void;

    /**
     * Record a logout event.
     *
     * @param  string                $userId    User UUID.
     * @param  string                $tenantId  Tenant UUID.
     * @param  string                $deviceId  Device identifier.
     * @param  string                $ipAddress IP address.
     * @param  string                $userAgent User-agent string.
     * @param  array<string, mixed>  $metadata  Extra event data.
     * @return void
     */
    public function logLogout(
        string $userId,
        string $tenantId,
        string $deviceId,
        string $ipAddress,
        string $userAgent,
        array $metadata = [],
    ): void;

    /**
     * Record a token refresh event.
     *
     * @param  string                $userId    User UUID.
     * @param  string                $tenantId  Tenant UUID.
     * @param  string                $deviceId  Device identifier.
     * @param  string                $ipAddress IP address.
     * @param  string                $userAgent User-agent string.
     * @param  array<string, mixed>  $metadata  Extra event data.
     * @return void
     */
    public function logTokenRefresh(
        string $userId,
        string $tenantId,
        string $deviceId,
        string $ipAddress,
        string $userAgent,
        array $metadata = [],
    ): void;

    /**
     * Record a global session revocation (revoke-all).
     *
     * @param  string                $userId    User UUID.
     * @param  string                $tenantId  Tenant UUID.
     * @param  string                $ipAddress IP address.
     * @param  string                $userAgent User-agent string.
     * @param  array<string, mixed>  $metadata  Extra event data.
     * @return void
     */
    public function logGlobalRevocation(
        string $userId,
        string $tenantId,
        string $ipAddress,
        string $userAgent,
        array $metadata = [],
    ): void;

    /**
     * Record a device-specific revocation.
     *
     * @param  string                $userId    User UUID.
     * @param  string                $tenantId  Tenant UUID.
     * @param  string                $deviceId  Revoked device identifier.
     * @param  string                $ipAddress IP address.
     * @param  string                $userAgent User-agent string.
     * @param  array<string, mixed>  $metadata  Extra event data.
     * @return void
     */
    public function logDeviceRevocation(
        string $userId,
        string $tenantId,
        string $deviceId,
        string $ipAddress,
        string $userAgent,
        array $metadata = [],
    ): void;

    /**
     * Record a suspicious activity detection event.
     *
     * @param  string|null           $userId    User UUID (null when unknown).
     * @param  string                $tenantId  Tenant UUID.
     * @param  string                $eventType Suspicious event type label.
     * @param  string                $ipAddress IP address.
     * @param  string                $userAgent User-agent string.
     * @param  array<string, mixed>  $metadata  Extra context (e.g., reason).
     * @return void
     */
    public function logSuspiciousActivity(
        ?string $userId,
        string $tenantId,
        string $eventType,
        string $ipAddress,
        string $userAgent,
        array $metadata = [],
    ): void;

    /**
     * Record a user registration event.
     *
     * @param  string                $newUserId  Newly created user UUID.
     * @param  string                $tenantId   Tenant UUID.
     * @param  string                $actorId    Admin UUID who created the user.
     * @param  string                $ipAddress  IP address.
     * @param  string                $userAgent  User-agent string.
     * @param  array<string, mixed>  $metadata   Extra event data.
     * @return void
     */
    public function logUserRegistration(
        string $newUserId,
        string $tenantId,
        string $actorId,
        string $ipAddress,
        string $userAgent,
        array $metadata = [],
    ): void;
}
