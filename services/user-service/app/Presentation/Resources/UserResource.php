<?php

declare(strict_types=1);

namespace App\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'tenant_id' => $this->tenant_id,
            'avatar' => $this->avatar,
            'bio' => $this->bio,
            'phone' => $this->phone,
            'address' => $this->address ?? [],
            'preferences' => $this->preferences ?? [],
            'notification_settings' => $this->notification_settings ?? [],
            'timezone' => $this->timezone,
            'locale' => $this->locale,
            'theme' => $this->theme,
            'extra_permissions' => $this->extra_permissions ?? [],
            'is_active' => $this->is_active,
            'roles' => $this->whenLoaded('roles', fn() => $this->roles->map(fn($role) => [
                'id' => $role->id,
                'name' => $role->name,
                'slug' => $role->slug,
                'permissions' => $role->permissions ?? [],
            ])),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
