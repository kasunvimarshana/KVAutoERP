<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeatureFlagResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'tenant_id'          => $this->tenant_id,
            'flag_key'           => $this->flag_key,
            'is_enabled'         => $this->is_enabled,
            'rollout_percentage' => $this->rollout_percentage,
            'conditions'         => $this->conditions,
            'description'        => $this->description,
            'metadata'           => $this->metadata,
            'created_at'         => $this->created_at?->toIso8601String(),
            'updated_at'         => $this->updated_at?->toIso8601String(),
        ];
    }
}
