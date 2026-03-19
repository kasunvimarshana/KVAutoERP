<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms a UserProfile model into an API response array.
 *
 * @mixin \App\Models\UserProfile
 */
final class UserProfileResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'tenant_id'       => $this->tenant_id,
            'organization_id' => $this->organization_id,
            'branch_id'       => $this->branch_id,
            'location_id'     => $this->location_id,
            'department_id'   => $this->department_id,
            'auth_user_id'    => $this->auth_user_id,
            'email'           => $this->email,
            'first_name'      => $this->first_name,
            'last_name'       => $this->last_name,
            'full_name'       => $this->full_name,
            'display_name'    => $this->display_name,
            'avatar_url'      => $this->avatar_url,
            'phone'           => $this->phone,
            'locale'          => $this->locale,
            'timezone'        => $this->timezone,
            'is_active'       => $this->is_active,
            'roles'           => $this->whenLoaded(
                'roles',
                fn () => RoleResource::collection($this->roles),
            ),
            'created_by'  => $this->created_by,
            'updated_by'  => $this->updated_by,
            'created_at'  => $this->created_at?->toIso8601String(),
            'updated_at'  => $this->updated_at?->toIso8601String(),
        ];
    }
}
