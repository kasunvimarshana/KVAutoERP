<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Domain\Order\Entities\Order;
use App\Domain\Order\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent Order Repository.
 */
class OrderRepository extends BaseRepository implements OrderRepositoryInterface
{
    protected array $searchableColumns  = ['id', 'customer_id'];
    protected array $filterableColumns  = ['tenant_id', 'status', 'saga_status', 'customer_id', 'currency', 'created_at'];
    protected string $defaultSortBy     = 'created_at';

    protected function resolveModel(): Model
    {
        return new Order();
    }

    public function all(array $params = []): LengthAwarePaginator|Collection
    {
        return parent::all($params);
    }

    public function find(string $id): ?Order
    {
        /** @var Order|null */
        return parent::find($id);
    }

    public function create(array $data): Order
    {
        /** @var Order */
        return parent::create($data);
    }

    public function update(string $id, array $data): Order
    {
        /** @var Order */
        return parent::update($id, $data);
    }

    public function delete(string $id): bool
    {
        return parent::delete($id);
    }
}
