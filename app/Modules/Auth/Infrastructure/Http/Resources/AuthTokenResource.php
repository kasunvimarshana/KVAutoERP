<?php
declare(strict_types=1);
namespace Modules\Auth\Infrastructure\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthTokenResource extends JsonResource {
    public function toArray($request): array {
        return $this->resource->toArray();
    }
}
