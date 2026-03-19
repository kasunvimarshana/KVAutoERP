<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * User response DTO — exposes safe public user attributes.
 *
 * Sensitive fields (password, device_sessions) are never included.
 *
 * @mixin User
 */
final class UserResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var User $user */
        $user = $this->resource;

        return [
            'id'              => $user->id,
            'tenant_id'       => $user->tenant_id,
            'organization_id' => $user->organization_id,
            'branch_id'       => $user->branch_id,
            'email'           => $user->email,
            'first_name'      => $user->first_name,
            'last_name'       => $user->last_name,
            'full_name'       => $user->full_name,
            'roles'           => $user->roles ?? [],
            'permissions'     => $user->permissions ?? [],
            'is_active'       => (bool) $user->is_active,
            'created_at'      => $user->created_at?->toIso8601String(),
            'updated_at'      => $user->updated_at?->toIso8601String(),
        ];
    }
}
