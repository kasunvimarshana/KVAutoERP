<?php declare(strict_types=1);
namespace Modules\Accounting\Domain\Entities;
class Account {
    public const TYPES = ['asset','liability','equity','income','expense'];
    public function __construct(
        private readonly ?int $id,
        private readonly int $tenantId,
        private readonly string $code,
        private readonly string $name,
        private readonly string $type,        // asset|liability|equity|income|expense
        private readonly string $subType,     // current_asset|fixed_asset|accounts_payable|accounts_receivable|bank|credit_card|etc
        private readonly ?int $parentId,
        private readonly bool $isActive,
        private readonly string $normalBalance, // debit|credit
        private readonly ?string $description,
    ) {}
    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getCode(): string { return $this->code; }
    public function getName(): string { return $this->name; }
    public function getType(): string { return $this->type; }
    public function getSubType(): string { return $this->subType; }
    public function getParentId(): ?int { return $this->parentId; }
    public function isActive(): bool { return $this->isActive; }
    public function getNormalBalance(): string { return $this->normalBalance; }
    public function getDescription(): ?string { return $this->description; }
}
