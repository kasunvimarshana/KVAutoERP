<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Domain\Auth\Entities\User as UserEntity;
use App\Domain\Auth\Repositories\UserRepositoryInterface;
use App\Infrastructure\Persistence\Models\User as UserModel;
use App\Shared\Base\BaseRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Eloquent User Repository.
 *
 * Concrete implementation of {@see UserRepositoryInterface} backed by the
 * Eloquent ORM.  Converts Eloquent models to pure domain entities.
 */
final class EloquentUserRepository extends BaseRepository implements UserRepositoryInterface
{
    /** @var class-string<UserModel> */
    protected string $modelClass = UserModel::class;

    /** Columns used for full-text search. */
    protected array $searchableColumns = ['name', 'email'];

    // ──────────────────────────────────────────────────────────────────────
    // UserRepositoryInterface
    // ──────────────────────────────────────────────────────────────────────

    /**
     * {@inheritDoc}
     */
    public function findByEmail(string $email): ?UserEntity
    {
        $model = UserModel::where('email', mb_strtolower(trim($email)))->first();

        return $model?->toDomainEntity();
    }

    /**
     * {@inheritDoc}
     */
    public function findByTenantAndEmail(string $tenantId, string $email): ?UserEntity
    {
        $model = UserModel::forTenant($tenantId)
            ->where('email', mb_strtolower(trim($email)))
            ->first();

        return $model?->toDomainEntity();
    }

    /**
     * {@inheritDoc}
     */
    public function findActiveByTenant(string $tenantId): array
    {
        return UserModel::forTenant($tenantId)
            ->active()
            ->get()
            ->map(fn (UserModel $m) => $m->toDomainEntity())
            ->all();
    }

    /**
     * {@inheritDoc}
     */
    public function assignRole(string $userId, string $role, string $tenantId): void
    {
        $model = $this->requireModel($userId);

        // Spatie's HasRoles will create the role if it doesn't exist.
        $model->assignRole($role);
    }

    /**
     * {@inheritDoc}
     */
    public function revokeRole(string $userId, string $role, string $tenantId): void
    {
        $model = $this->requireModel($userId);
        $model->removeRole($role);
    }

    // ──────────────────────────────────────────────────────────────────────
    // RepositoryInterface overrides
    // ──────────────────────────────────────────────────────────────────────

    /**
     * {@inheritDoc}
     *
     * Returns a domain entity array rather than a raw Eloquent array.
     */
    public function findById(string|int $id): ?array
    {
        $model = UserModel::find($id);

        return $model?->toDomainEntity()->toArray();
    }

    /**
     * {@inheritDoc}
     */
    public function findByTenant(string $tenantId, array $filters = []): array
    {
        $query = UserModel::forTenant($tenantId);
        $query = $this->applyFilters($query, $filters);

        return $query->get()
            ->map(fn (UserModel $m) => $m->toDomainEntity()->toArray())
            ->all();
    }

    // ──────────────────────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Retrieve a UserModel or throw ModelNotFoundException.
     */
    private function requireModel(string $userId): UserModel
    {
        $model = UserModel::find($userId);

        if ($model === null) {
            throw (new ModelNotFoundException())->setModel(UserModel::class, $userId);
        }

        return $model;
    }
}
