<?php

declare(strict_types=1);

namespace Modules\User\Domain\Entities;

use DateTimeInterface;

class User
{
    public function __construct(
        public ?int $id,
        public int $tenantId,
        public ?int $orgUnitId,
        public string $name,
        public string $email,
        public string $password,
        public ?string $avatar,
        public ?string $phone,
        public string $locale,
        public string $timezone,
        public string $status,
        public array $preferences,
        public ?DateTimeInterface $emailVerifiedAt,
        public ?DateTimeInterface $createdAt,
        public ?DateTimeInterface $updatedAt,
    ) {}
}
