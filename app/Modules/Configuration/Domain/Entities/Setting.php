<?php

declare(strict_types=1);

namespace Modules\Configuration\Domain\Entities;

final class Setting
{
    public const TYPE_STRING = 'string';
    public const TYPE_INT    = 'int';
    public const TYPE_FLOAT  = 'float';
    public const TYPE_BOOL   = 'bool';
    public const TYPE_JSON   = 'json';

    public const TYPES = [
        self::TYPE_STRING,
        self::TYPE_INT,
        self::TYPE_FLOAT,
        self::TYPE_BOOL,
        self::TYPE_JSON,
    ];

    public function __construct(
        public readonly int $id,
        public readonly ?int $tenantId,
        public readonly string $key,
        public readonly ?string $value,
        public readonly string $type,
        public readonly string $group,
        public readonly bool $isPublic,
        public readonly ?string $description,
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt,
    ) {}

    public function getValue(): mixed
    {
        if ($this->value === null) {
            return null;
        }

        return match ($this->type) {
            self::TYPE_INT   => (int) $this->value,
            self::TYPE_FLOAT => (float) $this->value,
            self::TYPE_BOOL  => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            self::TYPE_JSON  => json_decode($this->value, true),
            default          => $this->value,
        };
    }
}
