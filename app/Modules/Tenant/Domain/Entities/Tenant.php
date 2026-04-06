<?php
declare(strict_types=1);
namespace Modules\Tenant\Domain\Entities;

class Tenant
{
    public function __construct(
        private readonly int $id,
        private readonly string $name,
        private readonly string $slug,
        private readonly ?string $domain,
        private readonly string $email,
        private readonly ?string $phone,
        private readonly ?array $address,
        private readonly bool $isActive,
        private readonly ?int $planId,
        private readonly ?array $settings,
        private readonly ?\DateTimeImmutable $createdAt,
        private readonly ?\DateTimeImmutable $updatedAt,
    ) {}

    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getSlug(): string { return $this->slug; }
    public function getDomain(): ?string { return $this->domain; }
    public function getEmail(): string { return $this->email; }
    public function getPhone(): ?string { return $this->phone; }
    public function getAddress(): ?array { return $this->address; }
    public function isActive(): bool { return $this->isActive; }
    public function getPlanId(): ?int { return $this->planId; }
    public function getSettings(): ?array { return $this->settings; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
}
