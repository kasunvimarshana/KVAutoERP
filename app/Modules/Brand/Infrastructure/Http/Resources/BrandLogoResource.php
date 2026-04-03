<?php
declare(strict_types=1);
namespace Modules\Brand\Infrastructure\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class BrandLogoResource extends JsonResource
{
    public function toArray($request): array { return ['id' => $this->resource->getId(), 'brand_id' => $this->resource->getBrandId(), 'uuid' => $this->resource->getUuid(), 'file_path' => $this->resource->getFilePath()]; }
}
