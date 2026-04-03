<?php
declare(strict_types=1);
namespace Modules\Product\Infrastructure\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class ProductVariationResource extends JsonResource
{
    public function toArray($request): array { return ['id' => $this->resource->getId(), 'name' => $this->resource->getName(), 'sku' => $this->resource->getSku()->value()]; }
}
