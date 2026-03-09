<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Application\Auth\DTOs\UserDTO;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin UserDTO
 */
class UserResource extends JsonResource
{
    /** @var UserDTO */
    public $resource;

    public function __construct(UserDTO $resource)
    {
        parent::__construct($resource);
    }

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->resource->id,
            'tenant_id'         => $this->resource->tenantId,
            'organization_id'   => $this->resource->organizationId,
            'name'              => $this->resource->name,
            'email'             => $this->resource->email,
            'status'            => $this->resource->status,
            'email_verified'    => $this->resource->emailVerified,
            'two_factor_enabled' => $this->resource->twoFactorEnabled,
            'last_login_at'     => $this->resource->lastLoginAt?->toIso8601String(),
            'roles'             => $this->resource->roles,
            'permissions'       => $this->resource->permissions,
            'metadata'          => $this->resource->metadata,
            'created_at'        => $this->resource->createdAt->toIso8601String(),
            'updated_at'        => $this->resource->updatedAt->toIso8601String(),
        ];
    }
}
