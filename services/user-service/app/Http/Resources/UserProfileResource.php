<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'user_id'       => $this->user_id,
            'tenant_id'     => $this->tenant_id,
            'first_name'    => $this->first_name,
            'last_name'     => $this->last_name,
            'full_name'     => trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? '')),
            'date_of_birth' => $this->date_of_birth?->toDateString(),
            'gender'        => $this->gender,
            'bio'           => $this->bio,
            'address'       => $this->address,
            'city'          => $this->city,
            'country'       => $this->country,
            'timezone'      => $this->timezone,
            'language'      => $this->language,
            'preferences'   => $this->preferences,
            'metadata'      => $this->metadata,
            'created_at'    => $this->created_at?->toIso8601String(),
            'updated_at'    => $this->updated_at?->toIso8601String(),
        ];
    }
}
