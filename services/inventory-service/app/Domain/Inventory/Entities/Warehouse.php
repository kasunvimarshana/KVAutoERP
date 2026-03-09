<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Entities;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Warehouse domain entity.
 *
 * Represents a physical or logical storage location scoped to a tenant.
 */
final class Warehouse
{
    public function __construct(
        public readonly string $id,
        public readonly string $tenantId,
        public string $name,
        public string $code,
        public array $address,
        public bool $isActive,
        public ?int $capacity,
        public readonly DateTimeImmutable $createdAt,
        public DateTimeImmutable $updatedAt,
    ) {
        if (trim($this->name) === '') {
            throw new InvalidArgumentException('Warehouse name cannot be empty.');
        }

        if (trim($this->code) === '') {
            throw new InvalidArgumentException('Warehouse code cannot be empty.');
        }
    }

    /**
     * Construct a Warehouse from a raw array.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $address = $data['address'] ?? [];
        if (is_string($address)) {
            $address = json_decode($address, true) ?? [];
        }

        return new self(
            id: $data['id'],
            tenantId: $data['tenant_id'],
            name: $data['name'],
            code: strtoupper($data['code']),
            address: $address,
            isActive: (bool) ($data['is_active'] ?? true),
            capacity: isset($data['capacity']) ? (int) $data['capacity'] : null,
            createdAt: isset($data['created_at'])
                ? new DateTimeImmutable($data['created_at'])
                : new DateTimeImmutable(),
            updatedAt: isset($data['updated_at'])
                ? new DateTimeImmutable($data['updated_at'])
                : new DateTimeImmutable(),
        );
    }

    /**
     * Convert to plain array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'tenant_id'  => $this->tenantId,
            'name'       => $this->name,
            'code'       => $this->code,
            'address'    => $this->address,
            'is_active'  => $this->isActive,
            'capacity'   => $this->capacity,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Whether the warehouse has a defined capacity limit.
     */
    public function hasCapacityLimit(): bool
    {
        return $this->capacity !== null;
    }
}
