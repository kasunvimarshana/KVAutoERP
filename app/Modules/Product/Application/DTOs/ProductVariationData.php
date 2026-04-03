<?php
declare(strict_types=1);
namespace Modules\Product\Application\DTOs;
class ProductVariationData
{
    public int $product_id = 0;
    public int $tenant_id = 0;
    public string $sku = '';
    public string $name = '';
    public float $price = 0.0;
    public string $currency = 'USD';
    public array $attribute_values = [];
    public string $status = 'active';
    public int $sort_order = 0;
    public ?array $metadata = null;
    public static function fromArray(array $data): self { $dto = new self(); foreach($data as $k=>$v) if(property_exists($dto,$k)) $dto->$k=$v; return $dto; }
    public function toArray(): array { return get_object_vars($this); }
}
