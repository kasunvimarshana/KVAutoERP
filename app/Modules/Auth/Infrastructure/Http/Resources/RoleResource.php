<?php

declare(strict_types=1);

namespace Modules\Auth\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'tenant_id'   => $this->tenantId ?? $this->tenant_id ?? null,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'description' => $this->description,
            'is_system'   => $this->isSystem ?? $this->is_system ?? false,
            'permissions' => $this->permissions ?? [],
            'created_at'  => isset($this->created_at) ? (string) $this->created_at : null,
            'updated_at'  => isset($this->updated_at) ? (string) $this->updated_at : null,
        ];
    }
}
