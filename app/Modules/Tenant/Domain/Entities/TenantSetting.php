<?php

declare(strict_types=1);

namespace Modules\Tenant\Domain\Entities;

final class TenantSetting
{
    public const GROUP_GENERAL = 'general';

    public function __construct(
        public readonly int $id,
        public readonly int $tenantId,
        public readonly string $key,
        public readonly ?string $value,
        public readonly string $group,
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt,
    ) {}
}
