<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Events;

use DateTimeImmutable;

/**
 * Domain event raised when a tenant's database is successfully provisioned.
 */
final readonly class TenantProvisioned
{
    public DateTimeImmutable $timestamp;

    public function __construct(
        public string $tenantId,
        public string $databaseName,
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
            'event'         => 'tenant.provisioned',
            'tenant_id'     => $this->tenantId,
            'database_name' => $this->databaseName,
            'timestamp'     => $this->timestamp->format(DateTimeImmutable::ATOM),
        ];
    }
}
