<?php

declare(strict_types=1);

namespace Modules\Authorization\Domain\Entities;

use DateTimeInterface;

class UserRole
{
    public function __construct(
        public ?int $id,
        public int $userId,
        public int $roleId,
        public int $tenantId,
        public ?DateTimeInterface $createdAt,
        public ?DateTimeInterface $updatedAt,
    ) {}
}
