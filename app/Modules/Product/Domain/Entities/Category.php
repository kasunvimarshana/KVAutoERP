<?php

declare(strict_types=1);

namespace Modules\Product\Domain\Entities;

class Category
{
    public function __construct(
        private readonly ?int $id,
        private readonly ?int $tenantId,
        private readonly string $name,
        private readonly string $slug,
        private readonly ?int $parentId,
        private readonly string $path,
        private readonly int $level,
        private readonly ?string $description,
        private readonly bool $isActive,
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

    public function getName(): string
    {
        return $this->name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function isRoot(): bool
    {
        return $this->parentId === null;
    }
}
