<?php

namespace App\Domain\Order\Repositories;

interface OrderRepositoryInterface
{
    /**
     * Find a single order by its primary key.
     */
    public function findById(string|int $id): ?\App\Domain\Order\Entities\Order;

    /**
     * Find an order by its human-readable order number.
     */
    public function findByOrderNumber(string $orderNumber): ?\App\Domain\Order\Entities\Order;

    /**
     * Return all orders belonging to a specific customer (within a tenant).
     */
    public function findByCustomer(string $customerId, string $tenantId): \Illuminate\Database\Eloquent\Collection;

    /**
     * Look up the order associated with a saga run.
     */
    public function findBySagaId(string $sagaId): ?\App\Domain\Order\Entities\Order;

    /**
     * Return a paginated list of orders filtered by status for a tenant.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getByStatus(string $status, string $tenantId, int $perPage = 15);

    /**
     * Return a paginated list of all orders for a tenant.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllForTenant(string $tenantId, int $perPage = 15, array $filters = []);

    /**
     * Persist a new order and return the created model.
     */
    public function create(array $data): \App\Domain\Order\Entities\Order;

    /**
     * Update an existing order and return the updated model.
     */
    public function update(string|int $id, array $data): \App\Domain\Order\Entities\Order;

    /**
     * Delete (soft-delete) an order.
     */
    public function delete(string|int $id): bool;

    /**
     * Return aggregate statistics for a tenant's orders.
     *
     * Expected keys: total_orders, pending, confirmed, processing, completed,
     *                cancelled, failed, total_revenue, average_order_value
     */
    public function getOrderStatistics(string $tenantId): array;
}
