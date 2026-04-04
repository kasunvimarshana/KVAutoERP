<?php

declare(strict_types=1);

namespace Modules\Authorization\Infrastructure\Http\Resources;

use Modules\Authorization\Domain\Entities\Permission;
use Modules\Core\Infrastructure\Http\Resources\BaseResource;

class PermissionResource extends BaseResource
{
    public function toArray($request): array
    {
        /** @var Permission $permission */
        $permission = $this->resource;

        return [
            'id' => $permission->id,
            'name' => $permission->name,
            'slug' => $permission->slug,
            'module' => $permission->module,
            'description' => $permission->description,
            'created_at' => $permission->createdAt?->format('Y-m-d\TH:i:s\Z'),
            'updated_at' => $permission->updatedAt?->format('Y-m-d\TH:i:s\Z'),
        ];
    }
}
