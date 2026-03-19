<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                       => $this->id,
            'device_id'                => $this->device_id,
            'device_name'              => $this->device_name,
            'device_type'              => $this->device_type,
            'platform'                 => $this->platform,
            'ip_address'               => $this->ip_address,
            'last_activity_at'         => $this->last_activity_at?->toIso8601String(),
            'refresh_token_expires_at' => $this->refresh_token_expires_at?->toIso8601String(),
            'is_active'                => $this->is_active,
            'created_at'               => $this->created_at?->toIso8601String(),
        ];
    }
}
