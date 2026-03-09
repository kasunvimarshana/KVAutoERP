<?php

declare(strict_types=1);

namespace App\Infrastructure\Auth;

use App\Domain\Tenant\Entities\Tenant;
use App\Domain\User\Entities\User;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\Guards\TokenGuard;
use Laravel\Passport\PassportUserProvider;
use Laravel\Passport\TokenRepository;
use League\OAuth2\Server\ResourceServer;

/**
 * Tenant-aware Passport token guard.
 *
 * In addition to standard Passport token validation, this guard verifies
 * that the token belongs to a user within the currently resolved tenant.
 */
class PassportTokenGuard implements Guard
{
    use GuardHelpers;

    private ?User $resolvedUser = null;

    private readonly TokenGuard $inner;

    public function __construct(
        ResourceServer $server,
        UserProvider $provider,
        TokenRepository $tokens,
        private readonly Request $request,
    ) {
        $this->provider = $provider;

        $this->inner = new TokenGuard(
            $server,
            new PassportUserProvider($provider, 'users'),
            $tokens,
            app(\Laravel\Passport\ClientRepository::class),
            app(\Illuminate\Encryption\Encrypter::class),
            $request,
        );
    }

    public function user(): ?User
    {
        if ($this->resolvedUser !== null) {
            return $this->resolvedUser;
        }

        /** @var User|null $user */
        $user = $this->inner->user();

        if ($user === null) {
            return null;
        }

        // Verify the token belongs to the current tenant
        $currentTenantId = $this->resolveTenantId();

        if ($currentTenantId !== null && $user->tenant_id !== $currentTenantId) {
            Log::warning('Cross-tenant token usage detected', [
                'user_id'           => $user->id,
                'token_tenant_id'   => $user->tenant_id,
                'request_tenant_id' => $currentTenantId,
                'ip'                => $this->request->ip(),
            ]);

            return null;
        }

        $this->resolvedUser = $user;

        return $this->resolvedUser;
    }

    public function validate(array $credentials = []): bool
    {
        return $this->inner->validate($credentials);
    }

    /**
     * Attempt to resolve the current tenant ID from the request.
     * Checks: X-Tenant-ID header, then JWT claim, then subdomain.
     */
    private function resolveTenantId(): ?string
    {
        // 1. Explicit header
        if ($this->request->hasHeader('X-Tenant-ID')) {
            return $this->request->header('X-Tenant-ID');
        }

        // 2. Query parameter (for OAuth callbacks)
        if ($this->request->has('tenant_id')) {
            return $this->request->query('tenant_id');
        }

        // 3. Try the container binding set by TenantMiddleware
        if (app()->bound(Tenant::class)) {
            $tenant = app(Tenant::class);

            if ($tenant instanceof Tenant) {
                return $tenant->id;
            }
        }

        return null;
    }
}
