<?php declare(strict_types=1);
namespace Modules\Configuration\Domain\Entities;
class OrgUnit {
    public function __construct(
        private readonly ?int $id,
        private readonly int $tenantId,
        private readonly string $name,
        private readonly string $code,
        private readonly string $type, // company|division|department|team|branch|warehouse|region|other
        private readonly ?int $parentId,
        private readonly string $path, // materialized path like /1/2/3/
        private readonly int $level,
        private readonly bool $isActive,
    ) {}
    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getName(): string { return $this->name; }
    public function getCode(): string { return $this->code; }
    public function getType(): string { return $this->type; }
    public function getParentId(): ?int { return $this->parentId; }
    public function getPath(): string { return $this->path; }
    public function getLevel(): int { return $this->level; }
    public function isActive(): bool { return $this->isActive; }
    public function isDescendantOf(OrgUnit $other): bool {
        return str_starts_with($this->path, $other->getPath()) && $this->path !== $other->getPath();
    }
}
