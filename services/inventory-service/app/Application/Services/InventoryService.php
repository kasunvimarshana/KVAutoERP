<?php

namespace App\Application\Services;

use App\Application\Commands\CreateInventoryCommand;
use App\Application\Queries\GetInventoryQuery;
use App\Domain\Inventory\Events\InventoryCreated;
use App\Domain\Inventory\Events\StockDepleted;
use App\Domain\Inventory\Repositories\InventoryRepositoryInterface;
use App\Infrastructure\Messaging\Contracts\MessageBrokerInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryService
{
    public function __construct(
        private readonly InventoryRepositoryInterface $repository,
        private readonly MessageBrokerInterface       $broker
    ) {}

    // ─── CRUD ────────────────────────────────────────────────────────────────

    public function createInventory(CreateInventoryCommand $command): object
    {
        // Guard: unique SKU per tenant
        $existing = $this->repository->findBySku($command->sku, $command->tenantId);
        if ($existing !== null) {
            throw new \DomainException(
                "SKU [{$command->sku}] already exists for this tenant."
            );
        }

        $item = DB::transaction(function () use ($command) {
            return $this->repository->create([
                'tenant_id'       => $command->tenantId,
                'sku'             => $command->sku,
                'name'            => $command->name,
                'description'     => $command->description,
                'quantity'        => $command->quantity,
                'reserved_quantity' => 0,
                'unit_cost'       => $command->unitCost,
                'unit_price'      => $command->unitPrice,
                'category'        => $command->category,
                'location'        => $command->location,
                'min_stock_level' => $command->minStockLevel,
                'max_stock_level' => $command->maxStockLevel,
                'status'          => 'active',
                'metadata'        => $command->metadata,
            ]);
        });

        $this->publishEvent(
            config('messaging.topics.inventory_created', 'inventory.created'),
            InventoryCreated::make((string) $item->id, $command->tenantId, $item->toArray())
        );

        return $item;
    }

    public function updateInventory(string $id, string $tenantId, array $data): object
    {
        $item = $this->getOwnedItem($id, $tenantId);

        // Prevent SKU collision when changing SKU
        if (isset($data['sku']) && $data['sku'] !== $item->sku) {
            $conflict = $this->repository->findBySku($data['sku'], $tenantId);
            if ($conflict !== null) {
                throw new \DomainException("SKU [{$data['sku']}] already exists for this tenant.");
            }
        }

        $updated = DB::transaction(fn () => $this->repository->update($id, $data));

        $this->publishEvent(
            config('messaging.topics.inventory_updated', 'inventory.updated'),
            [
                'event'        => 'inventory.updated',
                'inventory_id' => $id,
                'tenant_id'    => $tenantId,
                'data'         => $updated->toArray(),
                'occurred_at'  => now()->toISOString(),
            ]
        );

        return $updated;
    }

    public function deleteInventory(string $id, string $tenantId): void
    {
        $this->getOwnedItem($id, $tenantId);
        $this->repository->delete($id);
    }

    public function getInventory(string $id, string $tenantId): object
    {
        return $this->getOwnedItem($id, $tenantId);
    }

    public function listInventory(GetInventoryQuery $query): Collection|LengthAwarePaginator
    {
        return $this->repository->paginateOrGet($query->toArray());
    }

    // ─── Stock operations ─────────────────────────────────────────────────────

    /**
     * Adjust stock level.
     *
     * @param  string  $operation  'set' | 'increment' | 'decrement'
     */
    public function adjustStock(string $id, string $tenantId, int $quantity, string $operation = 'increment'): object
    {
        $item = $this->getOwnedItem($id, $tenantId);

        $previousQty = $item->quantity;

        $success = $this->repository->updateStock($id, $quantity, $operation);

        if (! $success) {
            throw new \RuntimeException("Failed to adjust stock for inventory item [{$id}].");
        }

        $item->refresh();

        $this->publishEvent(
            config('messaging.topics.stock_adjusted', 'inventory.stock.adjusted'),
            [
                'event'             => 'inventory.stock.adjusted',
                'inventory_id'      => $id,
                'tenant_id'         => $tenantId,
                'sku'               => $item->sku,
                'previous_quantity' => $previousQty,
                'new_quantity'      => $item->quantity,
                'operation'         => $operation,
                'adjustment'        => $quantity,
                'occurred_at'       => now()->toISOString(),
            ]
        );

        // Emit depletion event when stock drops to zero
        if ($item->quantity <= 0 && $previousQty > 0) {
            $this->publishEvent(
                config('messaging.topics.stock_depleted', 'inventory.stock.depleted'),
                StockDepleted::make($id, $tenantId, $item->sku, $previousQty)->toArray()
            );
        }

        return $item;
    }

    public function reserveStock(string $id, string $tenantId, int $quantity): bool
    {
        $this->getOwnedItem($id, $tenantId);

        $reserved = $this->repository->reserveStock($id, $quantity);

        if ($reserved) {
            $this->publishEvent(
                config('messaging.topics.stock_reserved', 'inventory.stock.reserved'),
                [
                    'event'        => 'inventory.stock.reserved',
                    'inventory_id' => $id,
                    'tenant_id'    => $tenantId,
                    'quantity'     => $quantity,
                    'occurred_at'  => now()->toISOString(),
                ]
            );
        }

        return $reserved;
    }

    public function releaseStock(string $id, string $tenantId, int $quantity): bool
    {
        $this->getOwnedItem($id, $tenantId);

        $released = $this->repository->releaseStock($id, $quantity);

        if ($released) {
            $this->publishEvent(
                config('messaging.topics.stock_released', 'inventory.stock.released'),
                [
                    'event'        => 'inventory.stock.released',
                    'inventory_id' => $id,
                    'tenant_id'    => $tenantId,
                    'quantity'     => $quantity,
                    'occurred_at'  => now()->toISOString(),
                ]
            );
        }

        return $released;
    }

    // ─── Reports ──────────────────────────────────────────────────────────────

    public function getLowStockReport(string $tenantId): Collection
    {
        return $this->repository->getLowStockItems($tenantId);
    }

    // ─── Internal helpers ─────────────────────────────────────────────────────

    private function getOwnedItem(string $id, string $tenantId): object
    {
        $item = $this->repository->find($id);

        if ($item === null) {
            throw new ModelNotFoundException("Inventory item [{$id}] not found.");
        }

        if ((string) $item->tenant_id !== $tenantId) {
            throw new \DomainException("Inventory item [{$id}] does not belong to this tenant.");
        }

        return $item;
    }

    private function publishEvent(string $topic, object|array $event): void
    {
        $payload = is_array($event) ? $event : $event->toArray();

        try {
            $this->broker->publish($topic, $payload);
        } catch (\Throwable $e) {
            // Events must not block the main operation
            Log::error("[InventoryService] Failed to publish event to topic={$topic}: {$e->getMessage()}");
        }
    }
}
