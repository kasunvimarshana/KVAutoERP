<?php

declare(strict_types=1);

namespace Modules\Tax\Domain\Entities;

class TaxGroupRate
{
    public function __construct(
        private readonly ?int $id,
        private readonly int $taxGroupId,
        private readonly string $name,
        private readonly float $rate,
        private readonly string $type,
        private readonly int $priority,
        private readonly bool $isActive,
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTaxGroupId(): int
    {
        return $this->taxGroupId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRate(): float
    {
        return $this->rate;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function calculateTax(float $subtotal): float
    {
        if ($this->type === 'percentage') {
            return $subtotal * $this->rate / 100;
        }

        return $this->rate;
    }
}
