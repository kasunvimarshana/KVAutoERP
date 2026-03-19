<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms a ProductCategory model into an API response array.
 *
 * @mixin \App\Models\ProductCategory
 */
final class CategoryResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'tenant_id'   => $this->tenant_id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'description' => $this->description,
            'parent_id'   => $this->parent_id,
            'parent'      => $this->whenLoaded('parent', fn () => new CategoryResource($this->parent)),
            'children'    => $this->whenLoaded('children', fn () => CategoryResource::collection($this->children)),
            'is_active'   => $this->is_active,
            'sort_order'  => $this->sort_order,
            'created_by'  => $this->created_by,
            'updated_by'  => $this->updated_by,
            'created_at'  => $this->created_at?->toIso8601String(),
            'updated_at'  => $this->updated_at?->toIso8601String(),
        ];
    }
}
