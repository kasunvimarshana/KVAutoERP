<?php

declare(strict_types=1);

namespace Modules\Tenant\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'slug'       => $this->slug,
            'status'     => $this->status instanceof \BackedEnum ? $this->status->value : $this->status,
            'plan'       => $this->plan instanceof \BackedEnum ? $this->plan->value : $this->plan,
            'settings'   => $this->settings,
            'metadata'   => $this->metadata,
            'created_by' => $this->createdBy ?? $this->created_by ?? null,
            'updated_by' => $this->updatedBy ?? $this->updated_by ?? null,
            'created_at' => isset($this->created_at) ? (string) $this->created_at : null,
            'updated_at' => isset($this->updated_at) ? (string) $this->updated_at : null,
        ];
    }
}
