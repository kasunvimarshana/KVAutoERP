<?php

declare(strict_types=1);

namespace Modules\CRM\Domain\Entities;

class Contact
{
    public function __construct(
        private readonly ?int $id,
        private readonly ?int $tenantId,
        private readonly string $type,
        private readonly string $firstName,
        private readonly string $lastName,
        private readonly ?string $email,
        private readonly ?string $phone,
        private readonly ?string $mobile,
        private readonly ?string $company,
        private readonly ?string $jobTitle,
        private readonly array $address,
        private readonly array $tags,
        private readonly string $status,
        private readonly ?int $assignedTo,
        private readonly ?string $notes,
        private readonly array $metadata,
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

    public function getType(): string
    {
        return $this->type;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function getJobTitle(): ?string
    {
        return $this->jobTitle;
    }

    public function getAddress(): array
    {
        return $this->address;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getAssignedTo(): ?int
    {
        return $this->assignedTo;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function isCustomer(): bool
    {
        return $this->type === 'customer';
    }

    public function isSupplier(): bool
    {
        return $this->type === 'supplier';
    }
}
