<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Entities;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Category domain entity (not Eloquent).
 *
 * Represents a hierarchical product category scoped to a tenant.
 */
final class Category
{
    public function __construct(
        public readonly string $id,
        public readonly string $tenantId,
        public string $name,
        public string $slug,
        public ?string $parentId,
        public string $description,
        public bool $isActive,
        public readonly DateTimeImmutable $createdAt,
        public DateTimeImmutable $updatedAt,
    ) {
        if (trim($this->name) === '') {
            throw new InvalidArgumentException('Category name cannot be empty.');
        }

        if (trim($this->slug) === '') {
            throw new InvalidArgumentException('Category slug cannot be empty.');
        }
    }

    /**
     * Construct a Category entity from a raw array (e.g., from the database).
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            tenantId: $data['tenant_id'],
            name: $data['name'],
            slug: $data['slug'],
            parentId: $data['parent_id'] ?? null,
            description: $data['description'] ?? '',
            isActive: (bool) ($data['is_active'] ?? true),
            createdAt: isset($data['created_at'])
                ? new DateTimeImmutable($data['created_at'])
                : new DateTimeImmutable(),
            updatedAt: isset($data['updated_at'])
                ? new DateTimeImmutable($data['updated_at'])
                : new DateTimeImmutable(),
        );
    }

    /**
     * Convert the entity to a plain array for persistence / serialisation.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'tenant_id'   => $this->tenantId,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'parent_id'   => $this->parentId,
            'description' => $this->description,
            'is_active'   => $this->isActive,
            'created_at'  => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at'  => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Whether this category is a root category (no parent).
     */
    public function isRoot(): bool
    {
        return $this->parentId === null;
    }

    /**
     * Deactivate the category and return a new instance reflecting the change.
     */
    public function deactivate(): self
    {
        $clone = clone $this;
        $clone->isActive = false;
        $clone->updatedAt = new DateTimeImmutable();

        return $clone;
    }
}
