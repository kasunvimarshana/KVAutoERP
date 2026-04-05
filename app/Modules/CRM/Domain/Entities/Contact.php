<?php declare(strict_types=1);
namespace Modules\CRM\Domain\Entities;
class Contact {
    public function __construct(
        private readonly ?int $id,
        private readonly int $tenantId,
        private readonly string $type,
        private readonly string $name,
        private readonly ?string $email,
        private readonly ?string $phone,
        private readonly ?string $company,
        private readonly ?string $address,
        private readonly bool $isActive,
        private readonly ?array $metadata,
    ) {}
    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getType(): string { return $this->type; }
    public function getName(): string { return $this->name; }
    public function getEmail(): ?string { return $this->email; }
    public function getPhone(): ?string { return $this->phone; }
    public function getCompany(): ?string { return $this->company; }
    public function getAddress(): ?string { return $this->address; }
    public function isActive(): bool { return $this->isActive; }
    public function getMetadata(): ?array { return $this->metadata; }
}
