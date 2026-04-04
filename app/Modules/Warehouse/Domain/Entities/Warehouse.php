<?php

declare(strict_types=1);

namespace Modules\Warehouse\Domain\Entities;

class Warehouse
{
    public function __construct(
        public readonly int $id,
        public int $tenantId,
        public string $name,
        public string $code,
        public string $type,
        public ?array $address,
        public bool $isActive,
        public ?int $managerUserId,
        public ?int $createdBy = null,
        public ?int $updatedBy = null,
    ) {}
}
