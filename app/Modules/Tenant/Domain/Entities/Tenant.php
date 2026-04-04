<?php

declare(strict_types=1);

namespace Modules\Tenant\Domain\Entities;

use Modules\Tenant\Domain\ValueObjects\TenantPlan;
use Modules\Tenant\Domain\ValueObjects\TenantStatus;

class Tenant
{
    public function __construct(
        public readonly int $id,
        public string $name,
        public string $slug,
        public TenantStatus $status,
        public TenantPlan $plan,
        public ?array $settings,
        public ?array $metadata,
        public ?int $createdBy,
        public ?int $updatedBy,
    ) {}

    public function isActive(): bool
    {
        return $this->status === TenantStatus::ACTIVE;
    }

    public function isSuspended(): bool
    {
        return $this->status === TenantStatus::SUSPENDED;
    }

    public function suspend(): void
    {
        $this->status = TenantStatus::SUSPENDED;
    }

    public function activate(): void
    {
        $this->status = TenantStatus::ACTIVE;
    }
}
