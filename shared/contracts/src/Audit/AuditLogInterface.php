<?php

declare(strict_types=1);

namespace KvSaas\Contracts\Audit;

/**
 * Immutable, append-only audit log — tamper-evident by design.
 * Every microservice must write to this log for compliance.
 */
interface AuditLogInterface
{
    /**
     * Append an immutable audit entry.
     *
     * @param  array<string, mixed>  $context
     */
    public function log(
        string  $action,
        string  $entityType,
        string  $entityId,
        array   $context  = [],
        ?string $actorId  = null,
        ?string $tenantId = null,
    ): void;

    /**
     * Query audit log entries with filters.
     *
     * @param  array<string, mixed>  $filters
     * @return array<int, array<string, mixed>>
     */
    public function query(array $filters): array;
}
