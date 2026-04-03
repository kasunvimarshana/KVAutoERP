<?php
declare(strict_types=1);
namespace Modules\Product\Application\DTOs;

class ProductData
{
    public int $tenant_id = 0;
    public string $sku = '';
    public string $name = '';
    public float $price = 0.0;
    public string $currency = 'USD';
    public ?string $description = null;
    public ?string $category = null;
    public string $status = 'active';
    public string $type = 'physical';
    public ?array $units_of_measure = null;
    public ?array $product_attributes = null;
    public ?array $attributes = null;
    public ?array $metadata = null;

    public static function fromArray(array $data): self
    {
        $dto = new self();
        foreach ($data as $k => $v) {
            if (property_exists($dto, $k)) $dto->$k = $v;
        }
        return $dto;
    }

    public function toArray(): array { return get_object_vars($this); }

    public function rules(): array
    {
        return [
            'tenant_id'                      => 'required|integer',
            'sku'                            => 'required|string|max:255',
            'name'                           => 'required|string|max:255',
            'price'                          => 'required|numeric|min:0',
            'currency'                       => 'nullable|string|size:3',
            'description'                    => 'nullable|string',
            'category'                       => 'nullable|string',
            'status'                         => 'nullable|string|in:active,inactive',
            'type'                           => 'nullable|string|in:physical,service,digital,combo,variable',
            'product_attributes'             => 'nullable|array',
            'product_attributes.*.code'      => 'required_with:product_attributes|string',
            'product_attributes.*.name'      => 'required_with:product_attributes|string',
            'product_attributes.*.allowed_values' => 'nullable|array',
        ];
    }
}
