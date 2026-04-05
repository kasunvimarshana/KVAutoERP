<?php declare(strict_types=1);
namespace Modules\HR\Domain\Entities;
class Department {
    public function __construct(
        private readonly ?int $id,
        private readonly int $tenantId,
        private readonly string $name,
        private readonly string $code,
        private readonly ?int $parentId,
        private readonly ?int $managerId,
        private readonly bool $isActive,
    ) {}
    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getName(): string { return $this->name; }
    public function getCode(): string { return $this->code; }
    public function getParentId(): ?int { return $this->parentId; }
    public function getManagerId(): ?int { return $this->managerId; }
    public function isActive(): bool { return $this->isActive; }
}
