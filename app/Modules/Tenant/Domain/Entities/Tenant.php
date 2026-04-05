<?php

declare(strict_types=1);

namespace Modules\Tenant\Domain\Entities;

use DateTimeImmutable;

final class Tenant
{
    public function __construct(
        private readonly string $id,
        private readonly string $name,
        private readonly string $slug,
        private readonly string $plan,
        private readonly string $status,
        private readonly array $settings,
        private readonly ?DateTimeImmutable $createdAt = null,
        private readonly ?DateTimeImmutable $updatedAt = null,
    ) {}

    public function getId(): string { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getSlug(): string { return $this->slug; }
    public function getPlan(): string { return $this->plan; }
    public function getStatus(): string { return $this->status; }
    public function getSettings(): array { return $this->settings; }
    public function getCreatedAt(): ?DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?DateTimeImmutable { return $this->updatedAt; }

    public function isActive(): bool { return $this->status === 'active'; }
    public function isSuspended(): bool { return $this->status === 'suspended'; }
    public function isTrial(): bool { return $this->status === 'trial'; }
}
