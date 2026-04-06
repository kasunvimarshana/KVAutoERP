<?php
declare(strict_types=1);
namespace Modules\Tenant\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Core\Infrastructure\Persistence\Repositories\EloquentRepository;
use Modules\Tenant\Domain\RepositoryInterfaces\TenantRepositoryInterface;
use Modules\Tenant\Infrastructure\Persistence\Eloquent\Models\TenantModel;

class EloquentTenantRepository extends EloquentRepository implements TenantRepositoryInterface
{
    public function __construct(TenantModel $model)
    {
        parent::__construct($model);
    }

    public function findById(int $id): mixed
    {
        return $this->model->newQuery()->withoutGlobalScopes()->find($id);
    }

    public function findBySlug(string $slug): mixed
    {
        return $this->model->newQuery()->withoutGlobalScopes()
            ->where('slug', $slug)
            ->first();
    }

    public function findByDomain(string $domain): mixed
    {
        return $this->model->newQuery()->withoutGlobalScopes()
            ->where('domain', $domain)
            ->first();
    }

    public function findAll(int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        return $this->model->newQuery()->withoutGlobalScopes()
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
