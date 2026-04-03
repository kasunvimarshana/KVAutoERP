<?php
declare(strict_types=1);
namespace Modules\Product\Infrastructure\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class ComboItemResource extends JsonResource
{
    public function toArray($request): array { return ['id' => $this->resource->getId(), 'component_product_id' => $this->resource->getComponentProductId(), 'quantity' => $this->resource->getQuantity()]; }
}
