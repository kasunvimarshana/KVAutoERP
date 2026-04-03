<?php
declare(strict_types=1);
namespace Modules\Brand\Infrastructure\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class BrandResource extends JsonResource
{
    public function toArray($request): array { return ['id' => $this->resource->getId(), 'tenant_id' => $this->resource->getTenantId(), 'name' => $this->resource->getName(), 'slug' => $this->resource->getSlug(), 'status' => $this->resource->getStatus()]; }
}
