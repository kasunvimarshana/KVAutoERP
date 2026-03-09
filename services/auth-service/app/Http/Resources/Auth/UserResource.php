<?php

declare(strict_types=1);

namespace App\Http\Resources\Auth;

use App\Domain\User\Entities\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * User Resource.
 *
 * @mixin User
 */
class UserResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'email'        => $this->email,
            'status'       => $this->status,
            'tenant_id'    => $this->tenant_id,
            'last_login_at' => $this->last_login_at?->toISOString(),
            'roles'        => $this->whenLoaded('roles', fn () =>
                $this->roles->map(fn ($r) => ['id' => $r->id, 'name' => $r->name, 'display_name' => $r->display_name]),
            ),
            'permissions'  => $this->whenLoaded('permissions', fn () =>
                $this->permissions->pluck('name'),
            ),
            'tenant'       => $this->whenLoaded('tenant', fn () => [
                'id'   => $this->tenant->id,
                'name' => $this->tenant->name,
                'slug' => $this->tenant->slug,
            ]),
            'created_at'   => $this->created_at?->toISOString(),
        ];
    }
}
