<?php

declare(strict_types=1);

namespace Modules\Authorization\Infrastructure\Http\Resources;

use Modules\Authorization\Domain\Entities\Role;
use Modules\Core\Infrastructure\Http\Resources\BaseResource;

class RoleResource extends BaseResource
{
    public function toArray($request): array
    {
        /** @var Role $role */
        $role = $this->resource;

        return [
            'id' => $role->id,
            'tenant_id' => $role->tenantId,
            'name' => $role->name,
            'slug' => $role->slug,
            'description' => $role->description,
            'is_system' => $role->isSystem,
            'created_at' => $role->createdAt?->format('Y-m-d\TH:i:s\Z'),
            'updated_at' => $role->updatedAt?->format('Y-m-d\TH:i:s\Z'),
        ];
    }
}
