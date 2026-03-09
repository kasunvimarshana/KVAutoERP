<?php

declare(strict_types=1);

namespace App\Domain\Product\ValueObjects;

use InvalidArgumentException;

final class Dimensions
{
    private readonly float $length;
    private readonly float $width;
    private readonly float $height;
    private readonly string $unit;

    private const SUPPORTED_UNITS = ['cm', 'in', 'm', 'mm', 'ft'];

    public function __construct(float $length, float $width, float $height, string $unit = 'cm')
    {
        $this->validatePositive($length, 'length');
        $this->validatePositive($width, 'width');
        $this->validatePositive($height, 'height');
        $this->validateUnit($unit);

        $this->length = $length;
        $this->width = $width;
        $this->height = $height;
        $this->unit = strtolower($unit);
    }

    private function validatePositive(float $value, string $field): void
    {
        if ($value <= 0) {
            throw new InvalidArgumentException("Dimension '{$field}' must be positive.");
        }
    }

    private function validateUnit(string $unit): void
    {
        if (!in_array(strtolower($unit), self::SUPPORTED_UNITS, true)) {
            throw new InvalidArgumentException(
                "Unsupported unit: {$unit}. Supported: " . implode(', ', self::SUPPORTED_UNITS)
            );
        }
    }

    public function getLength(): float { return $this->length; }
    public function getWidth(): float { return $this->width; }
    public function getHeight(): float { return $this->height; }
    public function getUnit(): string { return $this->unit; }

    public function getVolume(): float
    {
        return $this->length * $this->width * $this->height;
    }

    public function equals(self $other): bool
    {
        return $this->length === $other->length
            && $this->width === $other->width
            && $this->height === $other->height
            && $this->unit === $other->unit;
    }

    public function toArray(): array
    {
        return [
            'length' => $this->length,
            'width'  => $this->width,
            'height' => $this->height,
            'unit'   => $this->unit,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (float) ($data['length'] ?? 0),
            (float) ($data['width'] ?? 0),
            (float) ($data['height'] ?? 0),
            (string) ($data['unit'] ?? 'cm'),
        );
    }

    public function __toString(): string
    {
        return "{$this->length} x {$this->width} x {$this->height} {$this->unit}";
    }
}
