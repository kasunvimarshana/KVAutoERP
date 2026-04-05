<?php

declare(strict_types=1);

namespace Modules\OrgUnit\Domain\Entities;

final class OrgUnit
{
    public const TYPE_COMPANY     = 'company';
    public const TYPE_DIVISION    = 'division';
    public const TYPE_DEPARTMENT  = 'department';
    public const TYPE_TEAM        = 'team';
    public const TYPE_BRANCH      = 'branch';
    public const TYPE_WAREHOUSE   = 'warehouse';
    public const TYPE_STORE       = 'store';
    public const TYPE_COST_CENTER = 'cost_center';

    public const TYPES = [
        self::TYPE_COMPANY,
        self::TYPE_DIVISION,
        self::TYPE_DEPARTMENT,
        self::TYPE_TEAM,
        self::TYPE_BRANCH,
        self::TYPE_WAREHOUSE,
        self::TYPE_STORE,
        self::TYPE_COST_CENTER,
    ];

    public function __construct(
        public readonly int $id,
        public readonly int $tenantId,
        public readonly ?int $parentId,
        public readonly string $name,
        public readonly string $code,
        public readonly string $type,
        public readonly string $path,
        public readonly int $level,
        public readonly bool $isActive,
        public readonly ?array $metadata,
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt,
    ) {}

    public function isRoot(): bool
    {
        return $this->parentId === null;
    }

    public function isDescendantOf(OrgUnit $ancestor): bool
    {
        return str_starts_with($this->path, $ancestor->path . $ancestor->id . '/');
    }
}
