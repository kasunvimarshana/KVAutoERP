<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'tenant_id'   => $this->tenant_id,
            'parent_id'   => $this->parent_id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'description' => $this->description,
            'status'      => $this->status,
            'settings'    => $this->settings,
            'metadata'    => $this->metadata,
            'is_active'   => $this->isActive(),
            'is_root'     => $this->isRoot(),
            'depth'       => $this->getDepth(),
            'children'    => $this->when(
                $this->relationLoaded('children'),
                fn () => self::collection($this->children)
            ),
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
        ];
    }
}
