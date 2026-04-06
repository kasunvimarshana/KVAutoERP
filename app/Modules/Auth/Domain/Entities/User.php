<?php
declare(strict_types=1);
namespace Modules\Auth\Domain\Entities;

class User
{
    public function __construct(
        private readonly int|string $id,
        private readonly int $tenantId,
        private readonly string $name,
        private readonly string $email,
        private readonly bool $isActive,
        private readonly ?array $roles,
        private readonly ?array $permissions,
        private readonly ?\DateTimeImmutable $lastLoginAt,
        private readonly ?\DateTimeImmutable $createdAt,
        private readonly ?\DateTimeImmutable $updatedAt,
    ) {}

    public function getId(): int|string { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getName(): string { return $this->name; }
    public function getEmail(): string { return $this->email; }
    public function isActive(): bool { return $this->isActive; }
    public function getRoles(): ?array { return $this->roles; }
    public function getPermissions(): ?array { return $this->permissions; }
    public function getLastLoginAt(): ?\DateTimeImmutable { return $this->lastLoginAt; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
}
