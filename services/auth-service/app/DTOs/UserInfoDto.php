<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class UserInfoDto
{
    public function __construct(
        public string  $externalId,
        public string  $email,
        public string  $name,
        public ?string $firstName  = null,
        public ?string $lastName   = null,
        public array   $attributes = [],
        public string  $provider   = 'unknown',
    ) {}
}
