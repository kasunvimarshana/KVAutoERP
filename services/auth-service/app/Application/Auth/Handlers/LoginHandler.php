<?php

declare(strict_types=1);

namespace App\Application\Auth\Handlers;

use App\Application\Auth\Commands\LoginCommand;
use App\Application\Auth\DTOs\TokenDTO;
use App\Application\Auth\DTOs\UserDTO;
use App\Contracts\Repositories\TenantRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Domain\User\Entities\User;
use App\Domain\User\Events\UserLoggedIn;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use RuntimeException;

final class LoginHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly TenantRepositoryInterface $tenantRepository,
    ) {}

    /**
     * @throws RuntimeException
     */
    public function handle(LoginCommand $command): TokenDTO
    {
        // 1. Resolve tenant
        $tenant = $this->tenantRepository->findById($command->tenantId);

        if ($tenant === null) {
            throw new RuntimeException("Tenant [{$command->tenantId}] not found.", 404);
        }

        if (! $tenant->isActive()) {
            throw new RuntimeException('Tenant is not active.', 403);
        }

        // 2. Validate credentials
        $user = $this->userRepository->findByEmail($command->email, $command->tenantId);

        if ($user === null || ! Hash::check($command->password, $user->password)) {
            // Uniform error message to prevent user enumeration
            throw new RuntimeException('The provided credentials are incorrect.', 401);
        }

        // 3. Check user status
        if ($user->isSuspended()) {
            throw new RuntimeException('Your account has been suspended. Please contact support.', 403);
        }

        if ($user->isInactive()) {
            throw new RuntimeException('Your account is inactive. Please contact support.', 403);
        }

        // 4. Determine token scopes from roles
        $scopes = $this->buildScopesForUser($user);

        // 5. Create Passport personal access token
        $tokenName = "tenant:{$command->tenantId}";
        $expiresIn = config('passport.token_lifetime', 60 * 24) * 60; // seconds

        $tokenResult = $user->createToken(
            $tokenName,
            $scopes,
            $command->remember
                ? now()->addDays(config('passport.refresh_token_lifetime', 30))
                : now()->addMinutes(config('passport.token_lifetime', 1440)),
        );

        // 6. Record login
        $user->recordLogin();

        // 7. Dispatch domain event
        Event::dispatch(new UserLoggedIn(
            $user,
            $command->tenantId,
            $command->ipAddress,
            $command->deviceInfo['user_agent'] ?? 'unknown',
        ));

        Log::info('User logged in', [
            'user_id'    => $user->id,
            'tenant_id'  => $command->tenantId,
            'ip_address' => $command->ipAddress,
        ]);

        return new TokenDTO(
            accessToken: $tokenResult->accessToken,
            tokenType: 'Bearer',
            expiresIn: $expiresIn,
            user: UserDTO::fromEntity($user),
        );
    }

    /**
     * Build OAuth scopes based on the user's roles.
     *
     * @return list<string>
     */
    private function buildScopesForUser(User $user): array
    {
        $scopeMap = [
            'super-admin' => ['*'],
            'admin'       => ['read', 'write', 'delete', 'manage-users'],
            'manager'     => ['read', 'write', 'manage-users'],
            'user'        => ['read', 'write'],
            'viewer'      => ['read'],
        ];

        $scopes = [];

        foreach ($user->getRoleNames() as $role) {
            $roleScopes = $scopeMap[$role] ?? ['read'];
            $scopes     = array_unique(array_merge($scopes, $roleScopes));
        }

        // Wildcard scope grants everything
        if (in_array('*', $scopes, true)) {
            return ['*'];
        }

        return array_values($scopes);
    }
}
