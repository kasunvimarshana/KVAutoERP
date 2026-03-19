<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantConfigurationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'tenant_id'    => $this->tenant_id,
            'service_name' => $this->service_name,
            'config_key'   => $this->config_key,
            'config_value' => $this->is_encrypted ? '***ENCRYPTED***' : $this->config_value,
            'config_type'  => $this->config_type,
            'typed_value'  => $this->is_encrypted ? null : $this->getTypedValue(),
            'is_encrypted' => $this->is_encrypted,
            'is_active'    => $this->is_active,
            'description'  => $this->description,
            'metadata'     => $this->metadata,
            'created_at'   => $this->created_at?->toIso8601String(),
            'updated_at'   => $this->updated_at?->toIso8601String(),
        ];
    }
}
