<?php declare(strict_types=1);
namespace Modules\Tax\Domain\Entities;
class TaxGroup {
    public function __construct(
        private readonly ?int $id,
        private readonly int $tenantId,
        private readonly string $name,
        private readonly string $code,
        private readonly string $type,   // inclusive|exclusive
        private readonly bool $isCompound, // compound tax
        private readonly bool $isActive,
    ) {}
    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getName(): string { return $this->name; }
    public function getCode(): string { return $this->code; }
    public function getType(): string { return $this->type; }
    public function isCompound(): bool { return $this->isCompound; }
    public function isActive(): bool { return $this->isActive; }
}
