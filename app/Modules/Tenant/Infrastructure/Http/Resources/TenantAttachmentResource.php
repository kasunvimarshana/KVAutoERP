<?php
declare(strict_types=1);
namespace Modules\Tenant\Infrastructure\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantAttachmentResource extends JsonResource {
    public function toArray($request): array {
        return [
            'id' => $this->resource->getId(),
            'uuid' => $this->resource->getUuid(),
            'name' => $this->resource->getName(),
            'file_path' => $this->resource->getFilePath(),
            'mime_type' => $this->resource->getMimeType(),
            'size' => $this->resource->getSize(),
            'type' => $this->resource->getType(),
        ];
    }
}
