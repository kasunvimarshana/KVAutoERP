<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'email'           => $this->email,
            'tenant_id'       => $this->tenant_id,
            'organisation_id' => $this->organisation_id,
            'branch_id'       => $this->branch_id,
            'location_id'     => $this->location_id,
            'department_id'   => $this->department_id,
            'is_active'       => $this->is_active,
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'last_login_at'   => $this->last_login_at?->toIso8601String(),
            'roles'           => $this->whenLoaded('roles', fn () => $this->roles->pluck('name')),
            'permissions'     => $this->when(
                $this->relationLoaded('roles'),
                fn () => collect($this->getAllPermissions()),
            ),
            'created_at'      => $this->created_at?->toIso8601String(),
        ];
    }
}
