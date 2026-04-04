<?php

declare(strict_types=1);

namespace Modules\Warehouse\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseLocationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'tenant_id'    => $this->tenantId ?? $this->tenant_id ?? null,
            'warehouse_id' => $this->warehouseId ?? $this->warehouse_id ?? null,
            'parent_id'    => $this->parentId ?? $this->parent_id ?? null,
            'name'         => $this->name,
            'code'         => $this->code,
            'type'         => $this->type,
            'barcode'      => $this->barcode,
            'capacity'     => $this->capacity,
            'is_active'    => $this->isActive ?? $this->is_active ?? true,
            'level'        => $this->level,
            'path'         => $this->path,
            'created_by'   => $this->createdBy ?? $this->created_by ?? null,
            'updated_by'   => $this->updatedBy ?? $this->updated_by ?? null,
            'children'     => isset($this->children) ? WarehouseLocationResource::collection($this->children) : [],
            'created_at'   => isset($this->created_at) ? (string) $this->created_at : null,
            'updated_at'   => isset($this->updated_at) ? (string) $this->updated_at : null,
        ];
    }
}
