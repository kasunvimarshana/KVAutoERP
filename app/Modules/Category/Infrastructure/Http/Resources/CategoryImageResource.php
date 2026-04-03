<?php
declare(strict_types=1);
namespace Modules\Category\Infrastructure\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryImageResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => $this->resource->getId(),
            'category_id' => $this->resource->getCategoryId(),
            'uuid'        => $this->resource->getUuid(),
            'name'        => $this->resource->getName(),
            'file_path'   => $this->resource->getFilePath(),
            'mime_type'   => $this->resource->getMimeType(),
            'size'        => $this->resource->getSize(),
        ];
    }
}
