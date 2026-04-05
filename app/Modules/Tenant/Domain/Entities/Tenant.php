<?php

declare(strict_types=1);

namespace Modules\Tenant\Domain\Entities;

class Tenant
{
    public function __construct(
        public readonly ?int $id,
        public string $name,
        public string $slug,
        public ?string $domain,
        public string $status,
        public string $plan,
        public array $settings,
        public array $metadata,
        public ?\DateTimeInterface $createdAt = null
    ) {
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function activate(): void
    {
        $this->status = 'active';
    }

    public function suspend(): void
    {
        $this->status = 'suspended';
    }
}
