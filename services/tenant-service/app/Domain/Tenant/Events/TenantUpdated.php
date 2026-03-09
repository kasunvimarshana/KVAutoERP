<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Events;

use DateTimeImmutable;

/**
 * Domain event raised when tenant attributes are updated.
 */
final readonly class TenantUpdated
{
    public DateTimeImmutable $timestamp;

    /**
     * @param  string              $tenantId      Tenant UUID.
     * @param  array<string,mixed> $changedFields Map of field names to new values.
     */
    public function __construct(
        public string $tenantId,
        public array $changedFields,
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
            'event'          => 'tenant.updated',
            'tenant_id'      => $this->tenantId,
            'changed_fields' => $this->changedFields,
            'timestamp'      => $this->timestamp->format(DateTimeImmutable::ATOM),
        ];
    }
}
