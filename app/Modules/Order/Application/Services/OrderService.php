<?php

declare(strict_types=1);

namespace App\Modules\Order\Application\Services;

use App\Core\Abstracts\Services\BaseService;
use App\Modules\Order\Application\Saga\Orchestrators\CreateOrderSagaOrchestrator;
use App\Modules\Order\Domain\Models\Order;
use App\Modules\Order\Infrastructure\Repositories\OrderRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

/**
 * OrderService
 *
 * Thin service that delegates order creation to the Saga orchestrator
 * and handles order queries / status transitions.
 */
class OrderService extends BaseService
{
    public function __construct(
        private readonly OrderRepository             $orderRepository,
        private readonly CreateOrderSagaOrchestrator $createOrderSaga
    ) {}

    // -------------------------------------------------------------------------
    //  Queries
    // -------------------------------------------------------------------------

    public function list(
        array $filters = [],
        array $sort = [],
        ?int $perPage = null,
        int $page = 1
    ): LengthAwarePaginator|Collection {
        return $this->orderRepository->all(
            filters: $filters,
            sort:    $sort,
            perPage: $perPage,
            page:    $page
        );
    }

    public function findById(int|string $id): Model
    {
        $order = $this->orderRepository->findById($id);

        if ($order === null) {
            throw new RuntimeException("Order [{$id}] not found.");
        }

        return $order;
    }

    // -------------------------------------------------------------------------
    //  Mutations
    // -------------------------------------------------------------------------

    /**
     * Create an order using the Saga orchestrator.
     *
     * @param  array<string,mixed> $data
     * @return Order
     */
    public function create(array $data): Order
    {
        return $this->createOrderSaga->execute($data);
    }

    /**
     * Cancel an order (with compensation if saga is in progress).
     */
    public function cancel(int|string $id): Model
    {
        $order = $this->findById($id);

        if (! $order->isCancellable()) {
            throw new RuntimeException("Order [{$id}] cannot be cancelled in status [{$order->status}].");
        }

        return $this->orderRepository->update($id, ['status' => Order::STATUS_CANCELLED]);
    }
}
