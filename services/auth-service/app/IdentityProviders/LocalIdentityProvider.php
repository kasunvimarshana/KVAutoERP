<?php

declare(strict_types=1);

namespace App\IdentityProviders;

use App\Contracts\IdentityProvider\IdentityProviderInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;

/**
 * Local identity provider — validates credentials against the auth-service database.
 *
 * This is the default IAM provider for tenants that manage credentials
 * locally rather than delegating to an external system (Okta, Keycloak, etc.).
 * It uses Argon2id password hashing (configured via `auth_service.password_algo`).
 */
final class LocalIdentityProvider implements IdentityProviderInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    /**
     * {@inheritDoc}
     *
     * Finds the user by email + tenant in the local `users` table and verifies
     * the password hash using the Argon2id algorithm.
     *
     * @param  string  $email
     * @param  string  $password  Plain-text password.
     * @param  string  $tenantId
     * @return array<string, mixed>|null  Identity map containing `user_id`, `email`,
     *                                    `tenant_id`, `is_active`; null on failure.
     */
    public function authenticate(string $email, string $password, string $tenantId): ?array
    {
        $user = $this->userRepository->findByEmailAndTenant($email, $tenantId);

        if ($user === null || !Hash::check($password, $user->password)) {
            return null;
        }

        return [
            'user_id'         => $user->id,
            'email'           => $user->email,
            'tenant_id'       => $user->tenant_id,
            'organization_id' => $user->organization_id,
            'branch_id'       => $user->branch_id,
            'is_active'       => $user->isActive(),
            'provider'        => $this->getProviderName(),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getProviderName(): string
    {
        return 'local';
    }

    /**
     * {@inheritDoc}
     *
     * The local provider supports all tenants unless they have been explicitly
     * configured to use an external provider exclusively.
     *
     * @param  string  $tenantId
     * @return bool
     */
    public function supports(string $tenantId): bool
    {
        return true;
    }
}
