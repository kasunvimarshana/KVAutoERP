<?php declare(strict_types=1);
namespace Modules\Currency\Domain\Entities;
class Currency {
    public function __construct(
        private readonly ?int $id,
        private readonly int $tenantId,
        private readonly string $code,   // ISO 4217 e.g. USD
        private readonly string $name,
        private readonly string $symbol,
        private readonly int $decimalPlaces,
        private readonly bool $isDefault,
        private readonly bool $isActive,
    ) {}
    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getCode(): string { return $this->code; }
    public function getName(): string { return $this->name; }
    public function getSymbol(): string { return $this->symbol; }
    public function getDecimalPlaces(): int { return $this->decimalPlaces; }
    public function isDefault(): bool { return $this->isDefault; }
    public function isActive(): bool { return $this->isActive; }
}
