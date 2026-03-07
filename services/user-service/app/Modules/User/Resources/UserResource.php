<?php

namespace App\Modules\User\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'keycloak_id' => $this->keycloak_id,
            'username'    => $this->username,
            'email'       => $this->email,
            'first_name'  => $this->first_name,
            'last_name'   => $this->last_name,
            'full_name'   => $this->full_name,
            'phone'       => $this->phone,
            'roles'       => $this->roles ?? [],
            'attributes'  => $this->attributes ?? [],
            'is_active'   => $this->is_active,
            'email_verified_at' => $this->email_verified_at?->toISOString(),
            'created_at'  => $this->created_at?->toISOString(),
            'updated_at'  => $this->updated_at?->toISOString(),
        ];
    }
}
