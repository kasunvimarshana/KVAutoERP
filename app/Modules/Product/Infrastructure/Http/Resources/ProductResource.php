<?php
declare(strict_types=1);
namespace Modules\Product\Infrastructure\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        $attrs = array_map(fn($a) => $a->toArray(), $this->resource->getProductAttributes());
        return [
            'id'                 => $this->resource->getId(),
            'tenant_id'          => $this->resource->getTenantId(),
            'sku'                => $this->resource->getSku()->value(),
            'name'               => $this->resource->getName(),
            'price'              => $this->resource->getPrice()->getAmount(),
            'currency'           => $this->resource->getPrice()->getCurrency(),
            'status'             => $this->resource->getStatus(),
            'type'               => $this->resource->getType()->value(),
            'product_attributes' => $attrs,
        ];
    }
}
