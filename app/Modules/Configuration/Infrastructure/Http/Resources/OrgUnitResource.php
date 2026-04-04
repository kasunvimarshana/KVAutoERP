<?php

declare(strict_types=1);

namespace Modules\Configuration\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrgUnitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'tenant_id'   => $this->tenantId ?? $this->tenant_id ?? null,
            'name'        => $this->name,
            'code'        => $this->code,
            'type'        => $this->type,
            'parent_id'   => $this->parentId ?? $this->parent_id ?? null,
            'description' => $this->description,
            'is_active'   => $this->isActive ?? $this->is_active ?? true,
            'metadata'    => $this->metadata,
            'created_by'  => $this->createdBy ?? $this->created_by ?? null,
            'updated_by'  => $this->updatedBy ?? $this->updated_by ?? null,
            'children'    => isset($this->children) ? OrgUnitResource::collection($this->children) : [],
            'created_at'  => isset($this->created_at) ? (string) $this->created_at : null,
            'updated_at'  => isset($this->updated_at) ? (string) $this->updated_at : null,
        ];
    }
}
