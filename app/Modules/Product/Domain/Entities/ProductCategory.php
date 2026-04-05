<?php declare(strict_types=1);
namespace Modules\Product\Domain\Entities;
class ProductCategory {
    public function __construct(
        private readonly ?int $id,
        private readonly int $tenantId,
        private readonly string $name,
        private readonly string $code,
        private readonly ?int $parentId,
        private readonly string $path,
        private readonly int $level,
        private readonly bool $isActive,
    ) {}
    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getName(): string { return $this->name; }
    public function getCode(): string { return $this->code; }
    public function getParentId(): ?int { return $this->parentId; }
    public function getPath(): string { return $this->path; }
    public function getLevel(): int { return $this->level; }
    public function isActive(): bool { return $this->isActive; }
}
