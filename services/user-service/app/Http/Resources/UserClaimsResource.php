<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms the claims array returned by UserProfileService::getClaimsForAuth()
 * into a standardised API response.
 *
 * Used exclusively by the InternalUserController to return enriched JWT claims
 * to the Auth Service.
 *
 * @mixin array<string, mixed>
 */
final class UserClaimsResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array<string, mixed> $claims */
        $claims = $this->resource;

        return [
            'user_id'         => $claims['user_id'] ?? null,
            'tenant_id'       => $claims['tenant_id'] ?? null,
            'organization_id' => $claims['organization_id'] ?? null,
            'branch_id'       => $claims['branch_id'] ?? null,
            'location_id'     => $claims['location_id'] ?? null,
            'department_id'   => $claims['department_id'] ?? null,
            'roles'           => $claims['roles'] ?? [],
            'permissions'     => $claims['permissions'] ?? [],
            'profile'         => $claims['profile'] ?? [],
        ];
    }
}
