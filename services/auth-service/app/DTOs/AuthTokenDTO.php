<?php

namespace App\DTOs;

use App\Models\User;

final class AuthTokenDTO
{
    public function __construct(
        public readonly string $accessToken,
        public readonly string $tokenType,
        public readonly int $expiresIn,
        public readonly array $user,
        public readonly array $tenant,
    ) {}

    public static function fromPassport(
        string $accessToken,
        User $user,
        array $tenantData,
        int $expiresIn = 0,
    ): self {
        return new self(
            accessToken: $accessToken,
            tokenType:   'Bearer',
            expiresIn:   $expiresIn,
            user:        [
                'id'          => $user->id,
                'name'        => $user->name,
                'email'       => $user->email,
                'role'        => $user->role,
                'permissions' => $user->permissions ?? [],
                'status'      => $user->status,
                'tenant_id'   => $user->tenant_id,
            ],
            tenant: $tenantData,
        );
    }

    public function toArray(): array
    {
        return [
            'access_token' => $this->accessToken,
            'token_type'   => $this->tokenType,
            'expires_in'   => $this->expiresIn,
            'user'         => $this->user,
            'tenant'       => $this->tenant,
        ];
    }
}
