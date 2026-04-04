<?php

declare(strict_types=1);

namespace Modules\Authorization\Domain\Entities;

use DateTimeInterface;

class Permission
{
    public function __construct(
        public ?int $id,
        public string $name,
        public string $slug,
        public string $module,
        public ?string $description,
        public ?DateTimeInterface $createdAt,
        public ?DateTimeInterface $updatedAt,
    ) {}
}
