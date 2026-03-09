<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Events;

use DateTimeImmutable;

/**
 * Domain event raised when a new tenant is created.
 */
final readonly class TenantCreated
{
    public DateTimeImmutable $timestamp;

    public function __construct(
        public string $tenantId,
        public string $name,
        public string $slug,
        public string $plan,
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
            'event'     => 'tenant.created',
            'tenant_id' => $this->tenantId,
            'name'      => $this->name,
            'slug'      => $this->slug,
            'plan'      => $this->plan,
            'timestamp' => $this->timestamp->format(DateTimeImmutable::ATOM),
        ];
    }
}
