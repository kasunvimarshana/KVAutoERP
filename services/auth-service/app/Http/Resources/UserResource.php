<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'tenant_id'    => $this->tenant_id,
            'name'         => $this->name,
            'email'        => $this->email,
            'status'       => $this->status,
            'roles'        => $this->whenLoaded('roles', fn() => $this->getRoleNames()),
            'permissions'  => $this->whenLoaded('permissions', fn() => $this->getAllPermissions()->pluck('name')),
            'last_login_at'=> $this->last_login_at?->toIso8601String(),
            'created_at'   => $this->created_at->toIso8601String(),
        ];
    }
}
