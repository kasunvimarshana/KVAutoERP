<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\TenantRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthService
{
    public function __construct(
        private readonly UserRepositoryInterface   $userRepository,
        private readonly TenantRepositoryInterface $tenantRepository,
    ) {}

    public function register(array $data): array
    {
        $tenant = $this->tenantRepository->findById($data['tenant_id']);

        if (!$tenant || !$tenant->isActive()) {
            throw new \RuntimeException('Tenant not found or inactive.', 422);
        }

        if ($this->userRepository->exists(['email' => $data['email']])) {
            throw new \RuntimeException('Email already in use.', 422);
        }

        $user = $this->userRepository->create([
            'tenant_id' => $data['tenant_id'],
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => $data['password'],
            'status'    => 'active',
        ]);

        $user->assignRole($data['role'] ?? 'user');

        $token = $user->createToken('auth-token', ['read', 'write'])->accessToken;

        Log::info('User registered', ['user_id' => $user->id, 'tenant_id' => $user->tenant_id]);

        return ['user' => $user->load('roles'), 'access_token' => $token, 'token_type' => 'Bearer'];
    }

    public function login(array $credentials): array
    {
        $user = $this->userRepository->findByEmail($credentials['email']);

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw new \RuntimeException('Invalid credentials.', 401);
        }

        if (!$user->isActive()) {
            throw new \RuntimeException('Account is inactive.', 403);
        }

        $token = $user->createToken('auth-token', ['read', 'write'])->accessToken;

        $this->userRepository->updateLastLogin($user->id);

        Log::info('User logged in', ['user_id' => $user->id]);

        return [
            'user'         => $user->load(['roles', 'permissions']),
            'access_token' => $token,
            'token_type'   => 'Bearer',
        ];
    }

    public function logout(User $user): void
    {
        $user->token()->revoke();
        Log::info('User logged out', ['user_id' => $user->id]);
    }

    public function me(User $user): User
    {
        return $user->load(['roles', 'permissions', 'tenant']);
    }

    public function changePassword(User $user, string $current, string $new): void
    {
        if (!Hash::check($current, $user->password)) {
            throw new \RuntimeException('Current password is incorrect.', 422);
        }
        $this->userRepository->update($user->id, ['password' => $new]);
    }
}
