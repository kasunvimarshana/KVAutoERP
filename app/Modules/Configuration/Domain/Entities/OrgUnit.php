<?php

declare(strict_types=1);

namespace Modules\Configuration\Domain\Entities;

final class OrgUnit
{
    public function __construct(
        private readonly string $id,
        private readonly string $tenantId,
        private readonly string $name,
        private readonly string $code,
        private readonly string $type,
        private readonly ?string $parentId,
        private readonly string $path,
        private readonly int $level,
        private readonly bool $isActive,
        private readonly array $metadata,
    ) {}

    public function getId(): string { return $this->id; }
    public function getTenantId(): string { return $this->tenantId; }
    public function getName(): string { return $this->name; }
    public function getCode(): string { return $this->code; }
    public function getType(): string { return $this->type; }
    public function getParentId(): ?string { return $this->parentId; }
    public function getPath(): string { return $this->path; }
    public function getLevel(): int { return $this->level; }
    public function isActive(): bool { return $this->isActive; }
    public function getMetadata(): array { return $this->metadata; }

    public function isRoot(): bool { return $this->parentId === null; }

    public function isDescendantOf(string $ancestorPath): bool
    {
        $normalized = rtrim($ancestorPath, '/').'/';

        // A node is NOT a descendant of itself; it must have a strictly longer path.
        return $this->path !== $normalized && str_starts_with($this->path, $normalized);
    }
}
