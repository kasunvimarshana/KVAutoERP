<?php
declare(strict_types=1);
namespace Modules\Tenant\Infrastructure\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantResource extends JsonResource {
    public function toArray($request): array {
        return ['id' => $this->resource->getId(), 'name' => $this->resource->getName()];
    }
}
