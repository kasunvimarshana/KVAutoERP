<?php
declare(strict_types=1);
namespace Modules\Product\Domain\ValueObjects;

class ProductAttribute
{
    private string $code;
    private string $name;
    private array $allowedValues;

    public function __construct(string $code, string $name, array $allowedValues = [])
    {
        if (trim($code) === '') throw new \InvalidArgumentException('Attribute code cannot be empty');
        if (trim($name) === '') throw new \InvalidArgumentException('Attribute name cannot be empty');
        $this->code          = strtolower(trim($code));
        $this->name          = $name;
        $this->allowedValues = $allowedValues;
    }

    public function getCode(): string { return $this->code; }
    public function getName(): string { return $this->name; }
    public function getAllowedValues(): array { return $this->allowedValues; }

    public function isValueAllowed(string $value): bool
    {
        if (empty($this->allowedValues)) return true;
        return in_array($value, $this->allowedValues, true);
    }

    public function equals(self $other): bool { return $this->code === $other->code; }

    public function toArray(): array
    {
        return ['code' => $this->code, 'name' => $this->name, 'allowed_values' => $this->allowedValues];
    }

    public static function fromArray(array $data): self
    {
        return new self($data['code'], $data['name'], $data['allowed_values'] ?? []);
    }
}
