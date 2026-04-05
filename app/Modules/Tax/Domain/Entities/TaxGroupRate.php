<?php declare(strict_types=1);
namespace Modules\Tax\Domain\Entities;
class TaxGroupRate {
    public function __construct(
        private readonly ?int $id,
        private readonly int $taxGroupId,
        private readonly string $name,
        private readonly float $rate, // percentage e.g. 10.0 = 10%
        private readonly int $sequence, // order for compound tax
    ) {}
    public function getId(): ?int { return $this->id; }
    public function getTaxGroupId(): int { return $this->taxGroupId; }
    public function getName(): string { return $this->name; }
    public function getRate(): float { return $this->rate; }
    public function getSequence(): int { return $this->sequence; }
}
