<?php

declare(strict_types=1);

namespace Modules\Tenant\Domain\Entities;

class Tenant
{
    public function __construct(
        private readonly ?int $id,
        private readonly string $name,
        private readonly string $slug,
        private readonly string $plan,
        private readonly string $status,
        private readonly ?string $domain,
        private readonly ?array $settings,
        private readonly \DateTimeInterface $createdAt,
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getPlan(): string
    {
        return $this->plan;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function getSettings(): ?array
    {
        return $this->settings;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
