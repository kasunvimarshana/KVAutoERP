<?php
declare(strict_types=1);
namespace Modules\Product\Domain\ValueObjects;

class ProductType
{
    public const VALID_TYPES = ['physical', 'service', 'digital', 'combo', 'variable'];

    private string $type;

    public function __construct(string $type)
    {
        if (!in_array($type, self::VALID_TYPES, true)) {
            throw new \InvalidArgumentException("Invalid product type: {$type}");
        }
        $this->type = $type;
    }

    public function value(): string { return $this->type; }
    public function isPhysical(): bool { return $this->type === 'physical'; }
    public function isService(): bool { return $this->type === 'service'; }
    public function isDigital(): bool { return $this->type === 'digital'; }
    public function isCombo(): bool { return $this->type === 'combo'; }
    public function isVariable(): bool { return $this->type === 'variable'; }

    public static function assertValid(string $type): void
    {
        if (!in_array($type, self::VALID_TYPES, true)) {
            throw new \InvalidArgumentException("Invalid product type: {$type}");
        }
    }
}
