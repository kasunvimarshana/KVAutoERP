<?php

declare(strict_types=1);

namespace App\Application\DTOs;

/**
 * UserProfile Data Transfer Object
 */
final class UserProfileDTO
{
    public function __construct(
        public readonly string|int $userId,
        public readonly string|int $tenantId,
        public readonly ?string $avatar = null,
        public readonly ?string $bio = null,
        public readonly ?string $phone = null,
        public readonly array $address = [],
        public readonly array $preferences = [],
        public readonly array $notificationSettings = [],
        public readonly string $timezone = 'UTC',
        public readonly string $locale = 'en',
        public readonly string $theme = 'light',
        public readonly array $extraPermissions = [],
        public readonly bool $isActive = true,
        public readonly array $metadata = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            userId: $data['user_id'],
            tenantId: $data['tenant_id'],
            avatar: $data['avatar'] ?? null,
            bio: $data['bio'] ?? null,
            phone: $data['phone'] ?? null,
            address: $data['address'] ?? [],
            preferences: $data['preferences'] ?? [],
            notificationSettings: $data['notification_settings'] ?? [],
            timezone: $data['timezone'] ?? 'UTC',
            locale: $data['locale'] ?? 'en',
            theme: $data['theme'] ?? 'light',
            extraPermissions: $data['extra_permissions'] ?? [],
            isActive: (bool) ($data['is_active'] ?? true),
            metadata: $data['metadata'] ?? [],
        );
    }
}
