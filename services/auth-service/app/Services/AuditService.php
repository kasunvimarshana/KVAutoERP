<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\AuditLogRepositoryInterface;
use App\Contracts\Services\AuditServiceInterface;
use App\Events\SuspiciousActivityDetected;
use Illuminate\Support\Facades\Log;

class AuditService implements AuditServiceInterface
{
    public function __construct(
        private readonly AuditLogRepositoryInterface $auditLogRepository,
    ) {}

    public function log(
        string $event,
        string $userId,
        string $tenantId,
        array $metadata = [],
        string $ipAddress = '',
        string $userAgent = '',
    ): void {
        $this->auditLogRepository->create([
            'event'      => $event,
            'user_id'    => $userId ?: null,
            'tenant_id'  => $tenantId ?: null,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'metadata'   => $metadata,
            'severity'   => $this->resolveSeverity($event),
            'created_at' => now(),
        ]);

        Log::channel('audit')->info("AUTH_AUDIT: {$event}", [
            'user_id'   => $userId,
            'tenant_id' => $tenantId,
            'ip'        => $ipAddress,
            'metadata'  => $metadata,
        ]);
    }

    public function logFailedLogin(
        string $email,
        string $tenantId,
        string $ipAddress,
        string $userAgent,
        string $reason,
    ): void {
        $this->auditLogRepository->create([
            'event'      => 'auth.login_failed',
            'user_id'    => null,
            'tenant_id'  => $tenantId ?: null,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'metadata'   => ['email' => $email, 'reason' => $reason],
            'severity'   => 'warning',
            'created_at' => now(),
        ]);
    }

    public function logSuspiciousActivity(
        string $userId,
        string $tenantId,
        string $activityType,
        array $context,
    ): void {
        $this->auditLogRepository->create([
            'event'      => 'auth.suspicious_activity',
            'user_id'    => $userId ?: null,
            'tenant_id'  => $tenantId ?: null,
            'ip_address' => $context['ip'] ?? '',
            'user_agent' => $context['user_agent'] ?? '',
            'metadata'   => array_merge(['activity_type' => $activityType], $context),
            'severity'   => 'critical',
            'created_at' => now(),
        ]);

        Log::channel('audit')->critical("SUSPICIOUS_ACTIVITY: {$activityType}", [
            'user_id'   => $userId,
            'tenant_id' => $tenantId,
            'context'   => $context,
        ]);

        event(new SuspiciousActivityDetected($userId, $tenantId, $activityType, $context));
    }

    public function isSuspiciousActivity(string $userId, string $ipAddress): bool
    {
        $windowMinutes = config('rate_limit.suspicious_activity.window_minutes', 15);
        $maxAttempts = config('rate_limit.suspicious_activity.max_failed_logins', 5);

        if ($ipAddress) {
            $ipFailures = $this->auditLogRepository->countFailedLoginsByIp($ipAddress, $windowMinutes);
            if ($ipFailures >= $maxAttempts * 2) {
                return true;
            }
        }

        if ($userId) {
            $userFailures = $this->auditLogRepository->countFailedLoginsByUser($userId, $windowMinutes);
            if ($userFailures >= $maxAttempts) {
                return true;
            }
        }

        return false;
    }

    private function resolveSeverity(string $event): string
    {
        return match (true) {
            str_contains($event, 'failed') || str_contains($event, 'suspicious') => 'warning',
            str_contains($event, 'locked') || str_contains($event, 'revoked')    => 'error',
            str_contains($event, 'deleted') || str_contains($event, 'breach')    => 'critical',
            default                                                                 => 'info',
        };
    }
}
