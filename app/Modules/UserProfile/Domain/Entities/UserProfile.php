<?php

declare(strict_types=1);

namespace Modules\UserProfile\Domain\Entities;

final class UserProfile
{
    public const DEFAULT_TIMEZONE = 'UTC';
    public const DEFAULT_LOCALE   = 'en';

    public function __construct(
        public readonly int $id,
        public readonly int $userId,
        public readonly ?string $avatar,
        public readonly ?string $bio,
        public readonly ?string $phone,
        public readonly ?array $address,
        public readonly ?array $preferences,
        public readonly string $timezone,
        public readonly string $locale,
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt,
    ) {}
}
