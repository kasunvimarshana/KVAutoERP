<?php

namespace App\Repositories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderRepository extends BaseRepository
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    public function findByTenant(string $tenantId): Collection
    {
        return $this->withTenant($tenantId)->all();
    }

    public function findByStatus(string $status): Collection
    {
        return $this->newQuery()->where('status', $status)->get();
    }

    public function getWithItems(string $orderId): ?Order
    {
        return $this->withRelations(['items'])->find($orderId);
    }

    public function getWithPagination(int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        return $this->withRelations(['items'])->newQuery()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
