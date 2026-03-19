<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ModuleRegistryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'tenant_id'     => $this->tenant_id,
            'module_name'   => $this->module_name,
            'module_key'    => $this->module_key,
            'is_enabled'    => $this->is_enabled,
            'configuration' => $this->configuration,
            'dependencies'  => $this->dependencies,
            'version'       => $this->version,
            'metadata'      => $this->metadata,
            'created_at'    => $this->created_at?->toIso8601String(),
            'updated_at'    => $this->updated_at?->toIso8601String(),
        ];
    }
}
