<?php

declare(strict_types=1);

namespace Modules\User\Application\DTOs;

readonly class UpdateUserData
{
    public function __construct(
        public ?string $name = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $locale = null,
        public ?string $timezone = null,
        public ?string $status = null,
    ) {}
}
