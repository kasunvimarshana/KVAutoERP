<?php
declare(strict_types=1);
namespace Modules\Product\Domain\ValueObjects;

class UnitOfMeasure
{
    public const VALID_TYPES = ['buying', 'selling', 'inventory'];

    private string $unit;
    private string $type;
    private float $conversionFactor;

    public function __construct(string $unit, string $type, float $conversionFactor)
    {
        if (!in_array($type, self::VALID_TYPES, true)) {
            throw new \InvalidArgumentException("Invalid UoM type: {$type}");
        }
        if ($conversionFactor <= 0) {
            throw new \InvalidArgumentException("Conversion factor must be > 0");
        }
        $this->unit             = $unit;
        $this->type             = $type;
        $this->conversionFactor = $conversionFactor;
    }

    public function getUnit(): string { return $this->unit; }
    public function getType(): string { return $this->type; }
    public function getConversionFactor(): float { return $this->conversionFactor; }
    public function isBuying(): bool { return $this->type === 'buying'; }
    public function isSelling(): bool { return $this->type === 'selling'; }
    public function isInventory(): bool { return $this->type === 'inventory'; }

    public function equals(self $other): bool
    {
        return $this->unit === $other->unit && $this->type === $other->type && $this->conversionFactor === $other->conversionFactor;
    }

    public function toArray(): array
    {
        return ['unit' => $this->unit, 'type' => $this->type, 'conversion_factor' => $this->conversionFactor];
    }

    public static function fromArray(array $data): self
    {
        return new self($data['unit'], $data['type'], $data['conversion_factor']);
    }
}
