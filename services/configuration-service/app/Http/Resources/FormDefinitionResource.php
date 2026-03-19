<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FormDefinitionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'tenant_id'    => $this->tenant_id,
            'service_name' => $this->service_name,
            'entity_type'  => $this->entity_type,
            'fields'       => $this->fields,
            'validations'  => $this->validations,
            'is_active'    => $this->is_active,
            'version'      => $this->version,
            'metadata'     => $this->metadata,
            'created_at'   => $this->created_at?->toIso8601String(),
            'updated_at'   => $this->updated_at?->toIso8601String(),
        ];
    }
}
