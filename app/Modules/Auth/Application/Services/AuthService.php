<?php

declare(strict_types=1);

namespace App\Modules\Auth\Application\Services;

use App\Core\Abstracts\Services\BaseService;
use App\Modules\Auth\Domain\Models\User;
use App\Modules\Auth\Infrastructure\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

/**
 * AuthService
 *
 * Handles Passport-based SSO authentication:
 *  - Login  → issue personal access token
 *  - Logout → revoke current token
 *  - Register → create tenant-scoped user
 *  - Refresh → handled by Passport internally
 */
class AuthService extends BaseService
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {}

    // -------------------------------------------------------------------------
    //  Login
    // -------------------------------------------------------------------------

    /**
     * Authenticate user and return a Passport personal access token.
     *
     * @param  string $email
     * @param  string $password
     * @return array{user: User, token: string, token_type: string}
     *
     * @throws RuntimeException on invalid credentials or inactive account
     */
    public function login(string $email, string $password): array
    {
        $user = $this->userRepository->findBy(['email' => $email]);

        if ($user === null || ! Hash::check($password, $user->password)) {
            throw new RuntimeException('Invalid credentials.', 401);
        }

        if (! $user->isActive()) {
            throw new RuntimeException('Account is inactive.', 403);
        }

        $token = $user->createToken('passport-token')->accessToken;

        return [
            'user'       => $user,
            'token'      => $token,
            'token_type' => 'Bearer',
        ];
    }

    // -------------------------------------------------------------------------
    //  Register
    // -------------------------------------------------------------------------

    /**
     * Register a new user within the current tenant context.
     *
     * @param  array<string,mixed> $data
     * @return array{user: User, token: string, token_type: string}
     */
    public function register(array $data): array
    {
        $user  = $this->userRepository->create($data);
        $token = $user->createToken('passport-token')->accessToken;

        return [
            'user'       => $user,
            'token'      => $token,
            'token_type' => 'Bearer',
        ];
    }

    // -------------------------------------------------------------------------
    //  Logout
    // -------------------------------------------------------------------------

    /**
     * Revoke the authenticated user's current access token.
     */
    public function logout(User $user): void
    {
        $user->token()->revoke();
    }

    // -------------------------------------------------------------------------
    //  Profile
    // -------------------------------------------------------------------------

    public function findById(int|string $id): ?User
    {
        return $this->userRepository->findById($id);
    }

    public function updateProfile(int|string $id, array $data): User
    {
        return $this->userRepository->update($id, $data);
    }
}
