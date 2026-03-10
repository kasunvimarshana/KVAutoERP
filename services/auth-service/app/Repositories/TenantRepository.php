<?php

namespace App\Repositories;

use App\Models\Tenant;
use App\Repositories\Contracts\TenantRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TenantRepository extends BaseRepository implements TenantRepositoryInterface
{
    public function __construct(Tenant $model)
    {
        parent::__construct($model);
    }

    protected function searchableColumns(): array
    {
        return ['name', 'slug', 'domain'];
    }

    protected function sortableColumns(): array
    {
        return ['name', 'slug', 'created_at', 'updated_at'];
    }

    public function findBySlug(string $slug): ?Tenant
    {
        return $this->model->where('slug', $slug)->first();
    }
}
