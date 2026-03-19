<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Services\AuditLogServiceInterface;
use App\Models\AuthAuditLog;
use Illuminate\Support\Facades\Log;

/**
 * Immutable, append-only authentication audit log service.
 *
 * All writes are fire-and-forget; failures are caught and logged to the
 * application log rather than bubbling up to disrupt auth flows.
 */
final class AuditLogService implements AuditLogServiceInterface
{
    // -------------------------------------------------------------------------
    // Event type constants
    // -------------------------------------------------------------------------

    public const EVENT_LOGIN              = 'auth.login';
    public const EVENT_LOGIN_FAILED       = 'auth.login.failed';
    public const EVENT_LOGOUT             = 'auth.logout';
    public const EVENT_TOKEN_REFRESH      = 'auth.token.refresh';
    public const EVENT_GLOBAL_REVOCATION  = 'auth.revoke.all';
    public const EVENT_DEVICE_REVOCATION  = 'auth.revoke.device';
    public const EVENT_SUSPICIOUS         = 'auth.suspicious';
    public const EVENT_USER_REGISTERED    = 'auth.user.registered';

    /**
     * {@inheritDoc}
     */
    public function logLogin(
        string $userId,
        string $tenantId,
        string $deviceId,
        string $ipAddress,
        string $userAgent,
        array $metadata = [],
    ): void {
        $this->write(
            eventType: self::EVENT_LOGIN,
            userId: $userId,
            tenantId: $tenantId,
            deviceId: $deviceId,
            ipAddress: $ipAddress,
            userAgent: $userAgent,
            metadata: $metadata,
        );
    }

    /**
     * {@inheritDoc}
     */
    public function logFailedLogin(
        ?string $userId,
        string $tenantId,
        string $email,
        string $ipAddress,
        string $userAgent,
        array $metadata = [],
    ): void {
        $this->write(
            eventType: self::EVENT_LOGIN_FAILED,
            userId: $userId,
            tenantId: $tenantId,
            deviceId: null,
            ipAddress: $ipAddress,
            userAgent: $userAgent,
            metadata: array_merge(['email' => $email], $metadata),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function logLogout(
        string $userId,
        string $tenantId,
        string $deviceId,
        string $ipAddress,
        string $userAgent,
        array $metadata = [],
    ): void {
        $this->write(
            eventType: self::EVENT_LOGOUT,
            userId: $userId,
            tenantId: $tenantId,
            deviceId: $deviceId,
            ipAddress: $ipAddress,
            userAgent: $userAgent,
            metadata: $metadata,
        );
    }

    /**
     * {@inheritDoc}
     */
    public function logTokenRefresh(
        string $userId,
        string $tenantId,
        string $deviceId,
        string $ipAddress,
        string $userAgent,
        array $metadata = [],
    ): void {
        $this->write(
            eventType: self::EVENT_TOKEN_REFRESH,
            userId: $userId,
            tenantId: $tenantId,
            deviceId: $deviceId,
            ipAddress: $ipAddress,
            userAgent: $userAgent,
            metadata: $metadata,
        );
    }

    /**
     * {@inheritDoc}
     */
    public function logGlobalRevocation(
        string $userId,
        string $tenantId,
        string $ipAddress,
        string $userAgent,
        array $metadata = [],
    ): void {
        $this->write(
            eventType: self::EVENT_GLOBAL_REVOCATION,
            userId: $userId,
            tenantId: $tenantId,
            deviceId: null,
            ipAddress: $ipAddress,
            userAgent: $userAgent,
            metadata: $metadata,
        );
    }

    /**
     * {@inheritDoc}
     */
    public function logDeviceRevocation(
        string $userId,
        string $tenantId,
        string $deviceId,
        string $ipAddress,
        string $userAgent,
        array $metadata = [],
    ): void {
        $this->write(
            eventType: self::EVENT_DEVICE_REVOCATION,
            userId: $userId,
            tenantId: $tenantId,
            deviceId: $deviceId,
            ipAddress: $ipAddress,
            userAgent: $userAgent,
            metadata: $metadata,
        );
    }

    /**
     * {@inheritDoc}
     */
    public function logSuspiciousActivity(
        ?string $userId,
        string $tenantId,
        string $eventType,
        string $ipAddress,
        string $userAgent,
        array $metadata = [],
    ): void {
        $this->write(
            eventType: self::EVENT_SUSPICIOUS . '.' . $eventType,
            userId: $userId,
            tenantId: $tenantId,
            deviceId: null,
            ipAddress: $ipAddress,
            userAgent: $userAgent,
            metadata: $metadata,
        );
    }

    /**
     * {@inheritDoc}
     */
    public function logUserRegistration(
        string $newUserId,
        string $tenantId,
        string $actorId,
        string $ipAddress,
        string $userAgent,
        array $metadata = [],
    ): void {
        $this->write(
            eventType: self::EVENT_USER_REGISTERED,
            userId: $actorId,
            tenantId: $tenantId,
            deviceId: null,
            ipAddress: $ipAddress,
            userAgent: $userAgent,
            metadata: array_merge(['new_user_id' => $newUserId], $metadata),
        );
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Persist an audit log record. Failures are silently logged.
     *
     * @param  string       $eventType
     * @param  string|null  $userId
     * @param  string|null  $tenantId
     * @param  string|null  $deviceId
     * @param  string|null  $ipAddress
     * @param  string|null  $userAgent
     * @param  array<string, mixed>  $metadata
     * @return void
     */
    private function write(
        string $eventType,
        ?string $userId,
        ?string $tenantId,
        ?string $deviceId,
        ?string $ipAddress,
        ?string $userAgent,
        array $metadata = [],
    ): void {
        try {
            AuthAuditLog::create([
                'user_id'    => $userId,
                'tenant_id'  => $tenantId,
                'event_type' => $eventType,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'device_id'  => $deviceId,
                'metadata'   => empty($metadata) ? null : $metadata,
            ]);
        } catch (\Throwable $e) {
            // Audit failures must never interrupt authentication flows.
            Log::error('[AuditLogService] Failed to write audit log', [
                'event_type' => $eventType,
                'user_id'    => $userId,
                'error'      => $e->getMessage(),
            ]);
        }
    }
}
