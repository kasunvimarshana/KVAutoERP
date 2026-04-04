<?php

declare(strict_types=1);

namespace Modules\User\Domain\Entities;

use Modules\User\Domain\ValueObjects\UserStatus;

class User
{
    public function __construct(
        public readonly int $id,
        public int $tenantId,
        public string $name,
        public string $email,
        public string $password,
        public ?string $avatar,
        public string $timezone,
        public string $locale,
        public UserStatus $status,
    ) {}

    public function isActive(): bool
    {
        return $this->status === UserStatus::ACTIVE;
    }
}
