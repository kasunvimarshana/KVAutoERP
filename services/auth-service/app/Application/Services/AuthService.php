<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Contracts\Repositories\UserRepositoryInterface;
use App\Application\Contracts\Services\AuthServiceInterface;
use App\Application\Contracts\Services\TenantConfigServiceInterface;
use App\Application\DTOs\LoginDTO;
use App\Application\DTOs\RegisterDTO;
use App\Application\DTOs\TokenDTO;
use App\Domain\Exceptions\AuthenticationException;
use App\Domain\Exceptions\TenantException;
use App\Domain\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\Token;

/**
 * Authentication Application Service
 * 
 * Orchestrates authentication business logic.
 * Thin controllers delegate all logic to this service.
 * 
 * Implements multi-tenant aware, stateless JWT/OAuth2 authentication.
 */
class AuthService implements AuthServiceInterface
{
    private const TOKEN_TTL_HOURS = 24;
    private const TOKEN_TTL_REMEMBER_DAYS = 30;
    private const TOKEN_TTL_SECONDS = 86400;           // 24h in seconds
    private const TOKEN_TTL_REMEMBER_SECONDS = 2592000; // 30d in seconds

    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly TenantConfigServiceInterface $tenantConfigService,
    ) {}

    /**
     * Authenticate a user and issue an access token.
     * 
     * @param LoginDTO $dto Login credentials
     * @return TokenDTO Token response
     * @throws AuthenticationException If credentials are invalid or tenant mismatch
     */
    public function login(LoginDTO $dto): TokenDTO
    {
        // Verify tenant exists and is active
        $tenant = $this->tenantConfigService->getActiveTenant($dto->tenantId);
        if (!$tenant) {
            throw TenantException::inactive($dto->tenantId);
        }

        // Find user by email within the tenant
        $user = $this->userRepository->findByEmailAndTenant($dto->email, $dto->tenantId);
        
        if (!$user || !Hash::check($dto->password, $user->password)) {
            Log::warning('Failed login attempt', [
                'email' => $dto->email,
                'tenant_id' => $dto->tenantId,
                'ip' => request()->ip(),
            ]);
            throw AuthenticationException::invalidCredentials();
        }

        if (!$user->isActive()) {
            throw AuthenticationException::accountInactive();
        }

        // Record login timestamp
        $user->recordLogin();

        // Issue Passport token
        $tokenResult = $user->createToken(
            name: $dto->deviceName ?? 'API Token',
            scopes: $this->getScopesForRole($user->role),
            expiresAt: $dto->remember
                ? now()->addDays(self::TOKEN_TTL_REMEMBER_DAYS)
                : now()->addHours(self::TOKEN_TTL_HOURS),
        );

        Log::info('User logged in', [
            'user_id' => $user->id,
            'tenant_id' => $dto->tenantId,
        ]);

        return new TokenDTO(
            accessToken: $tokenResult->accessToken,
            tokenType: 'Bearer',
            expiresIn: $dto->remember ? self::TOKEN_TTL_REMEMBER_SECONDS : self::TOKEN_TTL_SECONDS,
            user: $this->formatUserData($user),
        );
    }

    /**
     * Register a new user within a tenant.
     * 
     * @param RegisterDTO $dto Registration data
     * @return TokenDTO Token response
     */
    public function register(RegisterDTO $dto): TokenDTO
    {
        // Verify tenant
        $tenant = $this->tenantConfigService->getActiveTenant($dto->tenantId);
        if (!$tenant) {
            throw TenantException::notFound($dto->tenantId);
        }

        // Check if email already exists in this tenant
        if ($this->userRepository->existsByEmailAndTenant($dto->email, $dto->tenantId)) {
            throw new \InvalidArgumentException("Email already registered in this tenant.", 409);
        }

        // Create the user
        $user = $this->userRepository->create([
            'tenant_id' => $dto->tenantId,
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => $dto->password, // Will be hashed by the model cast
            'role' => $dto->role,
            'is_active' => true,
            'metadata' => $dto->metadata,
        ]);

        // Issue token
        $tokenResult = $user->createToken(
            name: 'API Token',
            scopes: $this->getScopesForRole($user->role),
            expiresAt: now()->addHours(self::TOKEN_TTL_HOURS),
        );

        Log::info('New user registered', [
            'user_id' => $user->id,
            'tenant_id' => $dto->tenantId,
        ]);

        return new TokenDTO(
            accessToken: $tokenResult->accessToken,
            tokenType: 'Bearer',
            expiresIn: self::TOKEN_TTL_SECONDS,
            user: $this->formatUserData($user),
        );
    }

    /**
     * Logout user by revoking all tokens.
     * 
     * @param User $user
     * @return bool
     */
    public function logout(User $user): bool
    {
        $user->tokens()->each(function (Token $token) {
            $token->revoke();
        });

        Log::info('User logged out', ['user_id' => $user->id]);
        return true;
    }

    /**
     * Refresh an access token using a refresh token.
     * 
     * @param string $refreshToken
     * @return TokenDTO
     */
    public function refreshToken(string $refreshToken): TokenDTO
    {
        // This is handled by Passport's built-in /oauth/token endpoint
        // Here we provide additional tenant validation
        throw new \RuntimeException('Use the /oauth/token endpoint for token refresh.');
    }

    /**
     * Validate a token and return the associated user.
     * 
     * @param string $token
     * @return User|null
     */
    public function validateToken(string $token): ?User
    {
        return $this->userRepository->findByToken($token);
    }

    /**
     * Introspect a token (for inter-service validation).
     * 
     * @param string $token
     * @return array
     */
    public function introspect(string $token): array
    {
        $user = $this->validateToken($token);
        
        if (!$user) {
            return ['active' => false];
        }

        return [
            'active' => true,
            'user_id' => $user->id,
            'tenant_id' => $user->tenant_id,
            'email' => $user->email,
            'role' => $user->role,
            'scopes' => $this->getScopesForRole($user->role),
        ];
    }

    /**
     * Get OAuth scopes for a given role.
     * 
     * @param string $role
     * @return array
     */
    private function getScopesForRole(string $role): array
    {
        return match($role) {
            'super_admin' => ['*'],
            'admin' => ['read', 'write', 'delete'],
            'manager' => ['read', 'write'],
            'user' => ['read'],
            default => ['read'],
        };
    }

    /**
     * Format user data for response.
     * 
     * @param User $user
     * @return array
     */
    private function formatUserData(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'tenant_id' => $user->tenant_id,
            'is_active' => $user->is_active,
            'last_login_at' => $user->last_login_at?->toISOString(),
        ];
    }
}
