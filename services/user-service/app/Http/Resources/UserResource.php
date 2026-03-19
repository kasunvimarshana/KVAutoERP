<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Support both plain arrays (from service layer) and Eloquent models
        // (both implement ArrayAccess, so bracket notation is safe for either).
        $r = $this->resource;

        return [
            'id'              => $r['id'] ?? null,
            'name'            => $r['name'] ?? null,
            'email'           => $r['email'] ?? null,
            'status'          => $r['status'] ?? null,
            'tenant_id'       => $r['tenant_id'] ?? null,
            'organization_id' => $r['organization_id'] ?? null,
            'branch_id'       => $r['branch_id'] ?? null,
            'roles'           => $r['roles'] ?? [],
            'permissions'     => $r['permissions'] ?? [],
            'token_version'   => $r['token_version'] ?? null,
            'iam_provider'    => $r['iam_provider'] ?? null,
            'avatar'          => $r['avatar'] ?? null,
            'phone'           => $r['phone'] ?? null,
            'last_login_at'   => $r['last_login_at'] ?? null,
            'created_at'      => $r['created_at'] ?? null,
        ];
    }
}
