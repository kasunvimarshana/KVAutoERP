<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\Entities;

final class ExpenseCategory
{
    public function __construct(
        private readonly string $id,
        private readonly string $tenantId,
        private readonly string $name,
        private readonly string $code,
        private readonly ?string $parentId,
        private readonly ?string $accountId,
        private readonly bool $isActive,
        private readonly ?string $description,
    ) {}

    public function getId(): string { return $this->id; }
    public function getTenantId(): string { return $this->tenantId; }
    public function getName(): string { return $this->name; }
    public function getCode(): string { return $this->code; }
    public function getParentId(): ?string { return $this->parentId; }
    public function getAccountId(): ?string { return $this->accountId; }
    public function isActive(): bool { return $this->isActive; }
    public function getDescription(): ?string { return $this->description; }
}
