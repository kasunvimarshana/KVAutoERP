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
            'tenant_id'       => $this->tenant_id,
            'organisation_id' => $this->organisation_id,
            'branch_id'       => $this->branch_id,
            'location_id'     => $this->location_id,
            'department_id'   => $this->department_id,
            'name'            => $this->name,
            'email'           => $this->email,
            'phone'           => $this->phone,
            'avatar'          => $this->avatar,
            'is_active'       => $this->is_active,
            'metadata'        => $this->metadata,
            'profile'         => $this->whenLoaded('profile', fn () => new UserProfileResource($this->profile)),
            'created_at'      => $this->created_at?->toIso8601String(),
            'updated_at'      => $this->updated_at?->toIso8601String(),
        ];
    }
}
