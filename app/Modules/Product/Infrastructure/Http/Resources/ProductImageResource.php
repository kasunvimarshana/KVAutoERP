<?php
declare(strict_types=1);
namespace Modules\Product\Infrastructure\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class ProductImageResource extends JsonResource
{
    public function toArray($request): array { return ['id' => $this->resource->getId(), 'uuid' => $this->resource->getUuid(), 'file_path' => $this->resource->getFilePath()]; }
}
