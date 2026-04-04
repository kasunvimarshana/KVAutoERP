<?php

declare(strict_types=1);

namespace Modules\Authorization\Domain\Entities;

use DateTimeInterface;

class Role
{
    public function __construct(
        public ?int $id,
        public int $tenantId,
        public string $name,
        public string $slug,
        public ?string $description,
        public bool $isSystem,
        public ?DateTimeInterface $createdAt,
        public ?DateTimeInterface $updatedAt,
    ) {}
}
