<?php

namespace App\Services;

use App\DTOs\AuthTokenDTO;
use App\Events\UserRegistered;
use App\Models\Tenant;
use App\Models\User;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Passport\PersonalAccessTokenResult;

class AuthService
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {}

    /**
     * Authenticate a user within a tenant context and issue a Passport token.
     *
     * @throws AuthenticationException
     */
    public function login(array $credentials, string $tenantId): array
    {
        $tenant = $this->resolveTenant($tenantId);

        $user = $this->userRepository->findByEmailAndTenant($credentials['email'], $tenantId);

        if ($user === null || !Hash::check($credentials['password'], $user->password)) {
            throw new AuthenticationException('Invalid credentials.');
        }

        if ($user->status !== 'active') {
            throw new AuthenticationException('Account is inactive or suspended.');
        }

        $tokenResult = $this->createPassportToken($user, $tenantId);

        return AuthTokenDTO::fromPassport(
            accessToken: $tokenResult->accessToken,
            user:        $user,
            tenantData:  $this->buildTenantData($tenant),
            expiresIn:   $this->tokenExpirySeconds($tokenResult),
        )->toArray();
    }

    /**
     * Register a new user within a tenant context.
     *
     * @throws \InvalidArgumentException
     */
    public function register(array $data): array
    {
        $tenant = $this->resolveTenant($data['tenant_id']);

        $existing = $this->userRepository->findByEmailAndTenant($data['email'], $data['tenant_id']);

        if ($existing !== null) {
            throw new \InvalidArgumentException('A user with this email already exists in this tenant.');
        }

        $user = $this->userRepository->transaction(function () use ($data): User {
            return $this->userRepository->create([
                'id'          => (string) Str::uuid(),
                'tenant_id'   => $data['tenant_id'],
                'name'        => $data['name'],
                'email'       => $data['email'],
                'password'    => Hash::make($data['password']),
                'role'        => $data['role']        ?? 'user',
                'permissions' => $data['permissions'] ?? [],
                'status'      => 'active',
            ]);
        });

        event(new UserRegistered($user, $tenant));

        $tokenResult = $this->createPassportToken($user, $data['tenant_id']);

        return AuthTokenDTO::fromPassport(
            accessToken: $tokenResult->accessToken,
            user:        $user,
            tenantData:  $this->buildTenantData($tenant),
            expiresIn:   $this->tokenExpirySeconds($tokenResult),
        )->toArray();
    }

    /**
     * Revoke all tokens for the given user.
     */
    public function logout(User $user): bool
    {
        $user->tokens()->each(function ($token): void {
            $token->revoke();
        });

        return true;
    }

    /**
     * Refresh a Passport token by revoking it and issuing a new one.
     *
     * @throws AuthenticationException
     */
    public function refresh(string $bearerToken): array
    {
        // Passport personal-access tokens are stored as plain random strings in
        // oauth_access_tokens. Look the token up directly in the database.
        $token = \Laravel\Passport\Token::where('id', hash('sha256', $bearerToken))->first()
            ?? \Laravel\Passport\Token::all()->first(
                fn ($t) => hash_equals(hash('sha256', $bearerToken), $t->id)
            );

        // Fall back: find any non-revoked token whose user matches the bearer value
        if ($token === null) {
            $oauthToken = DB::table('oauth_access_tokens')
                ->where('revoked', false)
                ->get()
                ->first(fn ($row) => hash_equals($row->id, substr($bearerToken, 0, 80)));

            if ($oauthToken !== null) {
                $token = \Laravel\Passport\Token::find($oauthToken->id);
            }
        }

        if ($token === null || $token->revoked) {
            throw new AuthenticationException('Token not found or already revoked.');
        }

        /** @var User $user */
        $user   = User::findOrFail($token->user_id);
        $tenant = $this->resolveTenant($user->tenant_id);

        $token->revoke();

        $tokenResult = $this->createPassportToken($user, $user->tenant_id);

        return AuthTokenDTO::fromPassport(
            accessToken: $tokenResult->accessToken,
            user:        $user,
            tenantData:  $this->buildTenantData($tenant),
            expiresIn:   $this->tokenExpirySeconds($tokenResult),
        )->toArray();
    }

    private function resolveTenant(string $tenantId): Tenant
    {
        $tenant = Tenant::where('id', $tenantId)
            ->orWhere('slug', $tenantId)
            ->first();

        if ($tenant === null || !$tenant->isActive()) {
            throw new \InvalidArgumentException("Tenant '{$tenantId}' not found or inactive.");
        }

        return $tenant;
    }

    private function createPassportToken(User $user, string $tenantId): PersonalAccessTokenResult
    {
        $days = (int) config('passport.token_expiry_days', 15);

        return $user->createToken('auth-token', ['*'], Carbon::now()->addDays($days));
    }

    private function buildTenantData(Tenant $tenant): array
    {
        return [
            'id'     => $tenant->id,
            'name'   => $tenant->name,
            'slug'   => $tenant->slug,
            'plan'   => $tenant->plan,
            'status' => $tenant->status,
        ];
    }

    private function tokenExpirySeconds(PersonalAccessTokenResult $result): int
    {
        $token = $result->token;

        if ($token === null || $token->expires_at === null) {
            return 0;
        }

        return (int) Carbon::now()->diffInSeconds($token->expires_at, false);
    }
}
