<?php declare(strict_types=1);
namespace Modules\Pricing\Domain\Entities;
class PriceList {
    public function __construct(
        private readonly ?int $id,
        private readonly int $tenantId,
        private readonly string $name,
        private readonly string $code,
        private readonly string $currency,
        private readonly bool $isDefault,
        private readonly bool $isActive,
    ) {}
    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getName(): string { return $this->name; }
    public function getCode(): string { return $this->code; }
    public function getCurrency(): string { return $this->currency; }
    public function isDefault(): bool { return $this->isDefault; }
    public function isActive(): bool { return $this->isActive; }
}
