<?php
declare(strict_types=1);
namespace Modules\OrganizationUnit\Infrastructure\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationUnitResource extends JsonResource {
    public function toArray($request): array {
        /** @var \Modules\OrganizationUnit\Domain\Entities\OrganizationUnit $unit */
        $unit = $this->resource;
        $code = $unit->getCode();
        return [
            'id' => $unit->getId(),
            'tenant_id' => $unit->getTenantId(),
            'name' => $unit->getName()->value(),
            'code' => $code !== null ? $code->value() : null,
            'type' => $unit->getType(),
            'parent_id' => $unit->getParentId(),
            'description' => $unit->getDescription(),
        ];
    }
}
