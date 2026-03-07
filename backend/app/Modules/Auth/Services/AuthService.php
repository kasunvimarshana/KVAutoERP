<?php

namespace App\Modules\Auth\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function login(array $credentials): ?array
    {
        $user = User::where('email', $credentials['email'])->with('tenant')->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return null;
        }

        if (!$user->is_active) {
            throw new \RuntimeException('Account is inactive.');
        }

        $token = $user->createToken('auth_token')->accessToken;

        return [
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => $user->load('roles', 'permissions'),
        ];
    }

    public function register(array $data): array
    {
        $user = User::create([
            'tenant_id' => $data['tenant_id'],
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => $data['password'],
            'is_active' => true,
        ]);

        $user->assignRole('user');

        $token = $user->createToken('auth_token')->accessToken;

        return [
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => $user->load('roles'),
        ];
    }

    public function logout(User $user): void
    {
        $user->token()->revoke();
    }

    public function refresh(User $user): array
    {
        $user->tokens()->where('revoked', false)->update(['revoked' => true]);
        $token = $user->createToken('auth_token')->accessToken;

        return [
            'access_token' => $token,
            'token_type'   => 'Bearer',
        ];
    }

    /** Generate a short-lived SSO token with the 'sso' scope. */
    public function generateSsoToken(User $user): string
    {
        return $user->createToken('sso_token', ['sso'])->accessToken;
    }
}
