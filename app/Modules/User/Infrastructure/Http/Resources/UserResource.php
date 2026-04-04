<?php

declare(strict_types=1);

namespace Modules\User\Infrastructure\Http\Resources;

use Modules\Core\Infrastructure\Http\Resources\BaseResource;
use Modules\User\Domain\Entities\User;

class UserResource extends BaseResource
{
    public function toArray($request): array
    {
        /** @var User $user */
        $user = $this->resource;

        return [
            'id' => $user->id,
            'tenant_id' => $user->tenantId,
            'org_unit_id' => $user->orgUnitId,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->avatar,
            'phone' => $user->phone,
            'locale' => $user->locale,
            'timezone' => $user->timezone,
            'status' => $user->status,
            'preferences' => $user->preferences,
            'email_verified_at' => $user->emailVerifiedAt?->format('Y-m-d\TH:i:s\Z'),
            'created_at' => $user->createdAt?->format('Y-m-d\TH:i:s\Z'),
            'updated_at' => $user->updatedAt?->format('Y-m-d\TH:i:s\Z'),
        ];
    }
}
