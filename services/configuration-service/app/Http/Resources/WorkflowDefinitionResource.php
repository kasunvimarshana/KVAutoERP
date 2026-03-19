<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkflowDefinitionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'tenant_id'   => $this->tenant_id,
            'name'        => $this->name,
            'entity_type' => $this->entity_type,
            'states'      => $this->states,
            'transitions' => $this->transitions,
            'guards'      => $this->guards,
            'actions'     => $this->actions,
            'is_active'   => $this->is_active,
            'version'     => $this->version,
            'metadata'    => $this->metadata,
            'created_at'  => $this->created_at?->toIso8601String(),
            'updated_at'  => $this->updated_at?->toIso8601String(),
        ];
    }
}
