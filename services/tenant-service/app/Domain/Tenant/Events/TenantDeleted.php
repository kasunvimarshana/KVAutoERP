<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Events;

use DateTimeImmutable;

/**
 * Domain event raised when a tenant is deleted (soft-deleted).
 */
final readonly class TenantDeleted
{
    public DateTimeImmutable $timestamp;

    public function __construct(
        public string $tenantId,
        ?DateTimeImmutable $timestamp = null,
    ) {
        $this->timestamp = $timestamp ?? new DateTimeImmutable();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'event'     => 'tenant.deleted',
            'tenant_id' => $this->tenantId,
            'timestamp' => $this->timestamp->format(DateTimeImmutable::ATOM),
        ];
    }
}
