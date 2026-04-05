<?php declare(strict_types=1);
namespace Modules\Auth\Domain\Entities;
class User {
    public function __construct(
        private readonly ?int $id,
        private readonly int $tenantId,
        private readonly string $name,
        private readonly string $email,
        private readonly string $passwordHash,
        private readonly string $role, // admin|manager|staff|customer|supplier
        private readonly bool $isActive,
        private readonly ?\DateTimeInterface $emailVerifiedAt,
        private readonly ?\DateTimeInterface $createdAt,
    ) {}
    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getName(): string { return $this->name; }
    public function getEmail(): string { return $this->email; }
    public function getPasswordHash(): string { return $this->passwordHash; }
    public function getRole(): string { return $this->role; }
    public function isActive(): bool { return $this->isActive; }
    public function getEmailVerifiedAt(): ?\DateTimeInterface { return $this->emailVerifiedAt; }
    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
}
