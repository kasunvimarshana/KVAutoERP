<?php

declare(strict_types=1);

namespace Modules\User\Application\DTOs;

readonly class CreateUserData
{
    public function __construct(
        public int $tenantId,
        public string $name,
        public string $email,
        public string $password,
        public ?int $orgUnitId = null,
        public ?string $phone = null,
        public string $locale = 'en',
        public string $timezone = 'UTC',
    ) {}
}
