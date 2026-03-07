<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'tenant_id'         => $this->tenant_id,
            'name'              => $this->name,
            'email'             => $this->email,
            'is_active'         => $this->is_active,
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'last_login_at'     => $this->last_login_at?->toIso8601String(),
            'metadata'          => $this->metadata,

            'roles' => $this->whenLoaded('roles', function () {
                return $this->getRoleNames();
            }),

            'permissions' => $this->whenLoaded('permissions', function () {
                return $this->getAllPermissions()->pluck('name');
            }),

            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
