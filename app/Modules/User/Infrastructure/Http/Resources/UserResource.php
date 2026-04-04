<?php

declare(strict_types=1);

namespace Modules\User\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'tenant_id' => $this->tenantId ?? $this->tenant_id ?? null,
            'name'      => $this->name,
            'email'     => $this->email,
            'avatar'    => $this->avatar,
            'timezone'  => $this->timezone,
            'locale'    => $this->locale,
            'status'    => $this->status instanceof \BackedEnum ? $this->status->value : $this->status,
            'email_verified_at' => isset($this->email_verified_at) ? (string) $this->email_verified_at : null,
            'created_at' => isset($this->created_at) ? (string) $this->created_at : null,
            'updated_at' => isset($this->updated_at) ? (string) $this->updated_at : null,
        ];
    }
}
