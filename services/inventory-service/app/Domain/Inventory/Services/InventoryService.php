<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Services;

use App\Domain\Inventory\Entities\InventoryItem;
use App\Domain\Inventory\Repositories\Interfaces\InventoryRepositoryInterface;
use App\Infrastructure\Webhooks\WebhookDispatcher;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Inventory Service.
 *
 * Core business logic for inventory management.
 * Pipeline pattern: all operations flow through this service.
 */
class InventoryService
{
    public function __construct(
        private readonly InventoryRepositoryInterface $inventoryRepository,
        private readonly WebhookDispatcher $webhookDispatcher,
    ) {}

    // =========================================================================
    // Inventory CRUD
    // =========================================================================

    /**
     * List inventory items with filtering, sorting, and pagination.
     *
     * @param  string                                        $tenantId
     * @param  array<string, mixed>                          $params
     * @return LengthAwarePaginator|Collection<int, InventoryItem>
     */
    public function list(string $tenantId, array $params = []): LengthAwarePaginator|Collection
    {
        $params['filters']['tenant_id'] = $tenantId;

        return $this->inventoryRepository->all($params);
    }

    /**
     * Get a specific inventory item.
     *
     * @param  string $id
     * @param  string $tenantId
     * @return InventoryItem
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getById(string $id, string $tenantId): InventoryItem
    {
        $item = $this->inventoryRepository->find($id);

        if ($item === null || $item->tenant_id !== $tenantId) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException("Inventory item [{$id}] not found.");
        }

        return $item;
    }

    /**
     * Create a new inventory item.
     *
     * @param  string               $tenantId
     * @param  array<string, mixed> $data
     * @return InventoryItem
     */
    public function create(string $tenantId, array $data): InventoryItem
    {
        $data['tenant_id']          = $tenantId;
        $data['reserved_quantity'] = $data['reserved_quantity'] ?? 0;
        $data['status']            = $data['status'] ?? 'active';

        $item = DB::transaction(fn (): InventoryItem => $this->inventoryRepository->create($data));

        // Dispatch webhook notification
        $this->webhookDispatcher->dispatch($tenantId, 'inventory.created', $item->toArray());

        return $item;
    }

    /**
     * Update an inventory item.
     *
     * @param  string               $id
     * @param  string               $tenantId
     * @param  array<string, mixed> $data
     * @return InventoryItem
     */
    public function update(string $id, string $tenantId, array $data): InventoryItem
    {
        $this->getById($id, $tenantId); // Ensure tenant ownership

        $item = DB::transaction(fn (): InventoryItem => $this->inventoryRepository->update($id, $data));

        $this->webhookDispatcher->dispatch($tenantId, 'inventory.updated', $item->toArray());

        return $item;
    }

    /**
     * Delete an inventory item.
     *
     * @param  string $id
     * @param  string $tenantId
     * @return bool
     */
    public function delete(string $id, string $tenantId): bool
    {
        $item = $this->getById($id, $tenantId);

        return DB::transaction(function () use ($id, $tenantId, $item): bool {
            $result = $this->inventoryRepository->delete($id);
            $this->webhookDispatcher->dispatch($tenantId, 'inventory.deleted', ['id' => $id]);

            return $result;
        });
    }

    // =========================================================================
    // Stock Management
    // =========================================================================

    /**
     * Reserve stock for an order (Saga step).
     *
     * @param  string $tenantId
     * @param  string $itemId
     * @param  int    $quantity
     * @param  string $orderId
     * @return InventoryItem
     * @throws \DomainException When insufficient stock
     */
    public function reserveStock(string $tenantId, string $itemId, int $quantity, string $orderId): InventoryItem
    {
        $item = $this->getById($itemId, $tenantId);

        $result = $this->inventoryRepository->reserveStock($itemId, $quantity, $orderId);

        $this->webhookDispatcher->dispatch($tenantId, 'inventory.stock_reserved', [
            'item_id'   => $itemId,
            'order_id'  => $orderId,
            'quantity'  => $quantity,
            'available' => $result->available_quantity,
        ]);

        return $result;
    }

    /**
     * Release reserved stock (Saga compensating transaction).
     *
     * Idempotent - safe to call multiple times.
     *
     * @param  string $tenantId
     * @param  string $itemId
     * @param  int    $quantity
     * @param  string $orderId
     * @return InventoryItem
     */
    public function releaseStock(string $tenantId, string $itemId, int $quantity, string $orderId): InventoryItem
    {
        $result = $this->inventoryRepository->releaseStock($itemId, $quantity, $orderId);

        $this->webhookDispatcher->dispatch($tenantId, 'inventory.stock_released', [
            'item_id'  => $itemId,
            'order_id' => $orderId,
            'quantity' => $quantity,
        ]);

        return $result;
    }

    /**
     * Get items that need restocking.
     *
     * @param  string $tenantId
     * @return Collection<int, InventoryItem>
     */
    public function getLowStockItems(string $tenantId): Collection
    {
        return Cache::remember(
            "inventory:low_stock:{$tenantId}",
            300, // 5 minutes cache
            fn () => $this->inventoryRepository->getLowStockItems($tenantId),
        );
    }
}
