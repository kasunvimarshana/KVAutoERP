<?php

declare(strict_types=1);

namespace KvSaas\Contracts\Auth\Dto;

/** Normalized user profile returned by any IAM provider adapter. */
final readonly class UserInfoDto
{
    public function __construct(
        public string  $externalId,
        public string  $email,
        public string  $name,
        public ?string $firstName  = null,
        public ?string $lastName   = null,
        /** @var array<string, mixed> */
        public array   $attributes = [],
        public string  $provider   = 'unknown',
    ) {}
}
