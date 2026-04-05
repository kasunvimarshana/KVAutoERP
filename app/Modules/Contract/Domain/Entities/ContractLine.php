<?php declare(strict_types=1);
namespace Modules\Contract\Domain\Entities;

class ContractLine
{
    public function __construct(
        private readonly ?int $id,
        private readonly int $contractId,
        private readonly string $description,
        private readonly float $quantity,
        private readonly float $unitPrice,
        private readonly float $taxRate,
        private readonly int $sortOrder,
    ) {}

    public function getId(): ?int { return $this->id; }
    public function getContractId(): int { return $this->contractId; }
    public function getDescription(): string { return $this->description; }
    public function getQuantity(): float { return $this->quantity; }
    public function getUnitPrice(): float { return $this->unitPrice; }
    public function getTaxRate(): float { return $this->taxRate; }
    public function getSortOrder(): int { return $this->sortOrder; }

    public function getLineTotal(): float
    {
        return $this->quantity * $this->unitPrice;
    }

    public function getTaxAmount(): float
    {
        return $this->getLineTotal() * ($this->taxRate / 100.0);
    }

    public function getGrossTotal(): float
    {
        return $this->getLineTotal() + $this->getTaxAmount();
    }
}
