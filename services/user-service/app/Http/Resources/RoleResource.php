<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->resource['id'] ?? $this->id,
            'name'        => $this->resource['name'] ?? $this->name,
            'slug'        => $this->resource['slug'] ?? $this->slug,
            'description' => $this->resource['description'] ?? $this->description,
            'tenant_id'   => $this->resource['tenant_id'] ?? $this->tenant_id,
            'is_system'   => $this->resource['is_system'] ?? $this->is_system,
            'permissions' => $this->resource['permissions'] ?? [],
        ];
    }
}
