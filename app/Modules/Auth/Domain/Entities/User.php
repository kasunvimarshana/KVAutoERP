<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\Entities;

use DateTimeImmutable;

final class User
{
    public function __construct(
        private readonly string $id,
        private readonly string $tenantId,
        private readonly string $name,
        private readonly string $email,
        private readonly string $role,
        private readonly string $status,
        private readonly array $preferences,
        private readonly ?DateTimeImmutable $lastLoginAt = null,
        private readonly ?DateTimeImmutable $createdAt = null,
    ) {}

    public function getId(): string { return $this->id; }
    public function getTenantId(): string { return $this->tenantId; }
    public function getName(): string { return $this->name; }
    public function getEmail(): string { return $this->email; }
    public function getRole(): string { return $this->role; }
    public function getStatus(): string { return $this->status; }
    public function getPreferences(): array { return $this->preferences; }
    public function getLastLoginAt(): ?DateTimeImmutable { return $this->lastLoginAt; }
    public function getCreatedAt(): ?DateTimeImmutable { return $this->createdAt; }

    public function isActive(): bool { return $this->status === 'active'; }
}
