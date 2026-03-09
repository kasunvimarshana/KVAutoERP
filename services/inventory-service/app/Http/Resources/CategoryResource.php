<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Category resource with child count and optional children.
 */
class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = is_array($this->resource) ? $this->resource : $this->resource->toArray();

        return [
            'id'          => $data['id'],
            'tenant_id'   => $data['tenant_id'],
            'name'        => $data['name'],
            'slug'        => $data['slug'],
            'parent_id'   => $data['parent_id'] ?? null,
            'description' => $data['description'] ?? '',
            'is_active'   => (bool) ($data['is_active'] ?? true),
            'created_at'  => $data['created_at'] ?? null,
            'updated_at'  => $data['updated_at'] ?? null,
        ];
    }
}
