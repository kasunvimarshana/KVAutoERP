<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\Entities;

class User
{
    public function __construct(
        private readonly ?int $id,
        private readonly ?int $tenantId,
        private readonly string $name,
        private readonly string $email,
        private readonly string $password,
        private readonly string $role,
        private readonly string $status,
        private readonly ?\DateTimeInterface $emailVerifiedAt,
        private readonly ?array $settings,
        private readonly \DateTimeInterface $createdAt,
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTenantId(): ?int
    {
        return $this->tenantId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getEmailVerifiedAt(): ?\DateTimeInterface
    {
        return $this->emailVerifiedAt;
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

    public function isEmailVerified(): bool
    {
        return $this->emailVerifiedAt !== null;
    }
}
