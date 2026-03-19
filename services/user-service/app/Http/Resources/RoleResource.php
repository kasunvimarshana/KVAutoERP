<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms a Role model into an API response array.
 *
 * @mixin \App\Models\Role
 */
final class RoleResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'tenant_id'       => $this->tenant_id,
            'name'            => $this->name,
            'slug'            => $this->slug,
            'description'     => $this->description,
            'hierarchy_level' => $this->hierarchy_level,
            'is_system'       => $this->is_system,
            'permissions'     => $this->whenLoaded(
                'permissions',
                fn () => PermissionResource::collection($this->permissions),
            ),
            'created_by'  => $this->created_by,
            'updated_by'  => $this->updated_by,
            'created_at'  => $this->created_at?->toIso8601String(),
            'updated_at'  => $this->updated_at?->toIso8601String(),
        ];
    }
}
