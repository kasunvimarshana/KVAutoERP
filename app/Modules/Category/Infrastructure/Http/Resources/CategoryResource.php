<?php
declare(strict_types=1);
namespace Modules\Category\Infrastructure\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => $this->resource->getId(),
            'tenant_id'   => $this->resource->getTenantId(),
            'name'        => $this->resource->getName(),
            'slug'        => $this->resource->getSlug(),
            'description' => $this->resource->getDescription(),
            'parent_id'   => $this->resource->getParentId(),
            'depth'       => $this->resource->getDepth(),
            'path'        => $this->resource->getPath(),
            'status'      => $this->resource->getStatus(),
        ];
    }
}
