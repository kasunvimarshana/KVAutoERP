<?php

declare(strict_types=1);

namespace Modules\Auth\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Contracts\OAuthenticatable;
use Modules\Auth\Application\Contracts\AuthUserRepositoryInterface;
use Modules\User\Infrastructure\Persistence\Eloquent\Models\UserModel;

/**
 * Eloquent-backed repository for auth-specific user lookups.
 *
 * Lives in the Auth module's infrastructure layer so that the application
 * layer (services, strategies) depends only on AuthUserRepositoryInterface.
 */
class EloquentAuthUserRepository implements AuthUserRepositoryInterface
{
    public function __construct(
        private readonly UserModel $model,
    ) {}

    public function findForPassport(int $userId): ?OAuthenticatable
    {
        /** @var OAuthenticatable|null $user */
        $user = $this->query()->find($userId);

        return $user;
    }

    public function findAuthenticatable(int $userId): ?Authenticatable
    {
        return $this->query()->find($userId);
    }

    public function getEmailById(int $userId): ?string
    {
        return $this->query()->find($userId)?->email;
    }

    public function getIdByEmail(string $email): ?int
    {
        $user = $this->query()->where('email', $email)->first();

        return $user?->id;
    }

    public function getRolesWithPermissions(int $userId): array
    {
        $user = $this->query()->with('roles.permissions')->find($userId);

        if (! $user) {
            return [];
        }

        return $user->roles->map(fn ($role) => [
            'name' => $role->name,
            'permissions' => $role->permissions->pluck('name')->toArray(),
        ])->toArray();
    }

    private function query(): Builder
    {
        $query = $this->model->newQuery();
        $tenantId = $this->resolveTenantId();

        if ($tenantId !== null) {
            $query->where('tenant_id', $tenantId);
        }

        return $query;
    }

    private function resolveTenantId(): ?int
    {
        $authenticatedTenantId = Auth::user()?->tenant_id;
        if (is_numeric($authenticatedTenantId) && (int) $authenticatedTenantId > 0) {
            return (int) $authenticatedTenantId;
        }

        $headerTenantId = request()?->header('X-Tenant-ID');
        if (is_numeric($headerTenantId) && (int) $headerTenantId > 0) {
            return (int) $headerTenantId;
        }

        $payloadTenantId = request()?->input('tenant_id');
        if (is_numeric($payloadTenantId) && (int) $payloadTenantId > 0) {
            return (int) $payloadTenantId;
        }

        return null;
    }
}
