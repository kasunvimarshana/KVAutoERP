<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Shared\Base\BaseResource;
use Illuminate\Http\Request;

/**
 * User API Resource.
 *
 * Transforms a User model or domain array into the standard API shape,
 * excluding sensitive fields such as the password hash.
 */
final class UserResource extends BaseResource
{
    /**
     * {@inheritDoc}
     */
    protected function resourceData(Request $request): array
    {
        return [
            'tenant_id'   => $this->attr('tenant_id'),
            'name'        => $this->attr('name'),
            'email'       => $this->attr('email'),
            'roles'       => $this->attr('roles', []),
            'permissions' => $this->attr('permissions', []),
            'is_active'   => $this->attr('is_active', true),
        ];
    }
}
