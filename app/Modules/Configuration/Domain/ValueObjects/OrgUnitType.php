<?php

declare(strict_types=1);

namespace Modules\Configuration\Domain\ValueObjects;

class OrgUnitType
{
    public const COMPANY = 'company';
    public const DIVISION = 'division';
    public const DEPARTMENT = 'department';
    public const BRANCH = 'branch';
    public const TEAM = 'team';

    public const VALID_TYPES = [
        self::COMPANY,
        self::DIVISION,
        self::DEPARTMENT,
        self::BRANCH,
        self::TEAM,
    ];

    public static function assertValid(string $type): void
    {
        if (!in_array($type, self::VALID_TYPES, true)) {
            throw new \InvalidArgumentException("Invalid OrgUnit type: {$type}");
        }
    }
}
