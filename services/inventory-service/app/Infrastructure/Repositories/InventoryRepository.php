<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Domain\Inventory\Entities\InventoryItem;
use App\Domain\Inventory\Entities\StockMovement;
use App\Domain\Inventory\Repositories\Interfaces\InventoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Eloquent Inventory Repository Implementation.
 *
 * Extends the reusable BaseRepository and adds inventory-specific operations.
 */
class InventoryRepository extends BaseRepository implements InventoryRepositoryInterface
{
    protected array $searchableColumns = ['name', 'sku', 'description'];

    protected array $filterableColumns = [
        'tenant_id',
        'category_id',
        'warehouse_id',
        'status',
        'sku',
        'unit_of_measure',
        'created_at',
        'updated_at',
    ];

    protected function resolveModel(): Model
    {
        return new InventoryItem();
    }

    // =========================================================================
    // InventoryRepositoryInterface Implementation
    // =========================================================================

    public function all(array $params = []): LengthAwarePaginator|Collection
    {
        return parent::all($params);
    }

    public function find(string $id): ?InventoryItem
    {
        /** @var InventoryItem|null */
        return parent::find($id);
    }

    public function findBySku(string $sku, string $tenantId): ?InventoryItem
    {
        return $this->findOneBy(['sku' => $sku, 'tenant_id' => $tenantId]);
    }

    public function create(array $data): InventoryItem
    {
        /** @var InventoryItem */
        return parent::create($data);
    }

    public function update(string $id, array $data): InventoryItem
    {
        /** @var InventoryItem */
        return parent::update($id, $data);
    }

    public function delete(string $id): bool
    {
        return parent::delete($id);
    }

    /**
     * Reserve stock with full audit trail.
     */
    public function reserveStock(string $itemId, int $quantity, string $referenceId): InventoryItem
    {
        return DB::transaction(function () use ($itemId, $quantity, $referenceId): InventoryItem {
            /** @var InventoryItem $item */
            $item = $this->newQuery()->lockForUpdate()->findOrFail($itemId);

            $beforeQty = $item->quantity;
            $item->reserveStock($quantity); // Throws DomainException on insufficient stock

            // Record audit trail
            StockMovement::create([
                'tenant_id'         => $item->tenant_id,
                'inventory_item_id' => $item->id,
                'type'              => 'reservation',
                'quantity'          => $quantity,
                'before_quantity'   => $beforeQty,
                'after_quantity'    => $item->fresh()->quantity,
                'reference_type'    => 'order',
                'reference_id'      => $referenceId,
                'notes'             => "Stock reserved for order {$referenceId}",
            ]);

            return $item->fresh() ?? $item;
        });
    }

    /**
     * Release reserved stock with audit trail.
     */
    public function releaseStock(string $itemId, int $quantity, string $referenceId): InventoryItem
    {
        return DB::transaction(function () use ($itemId, $quantity, $referenceId): InventoryItem {
            /** @var InventoryItem $item */
            $item = $this->newQuery()->lockForUpdate()->findOrFail($itemId);

            $beforeQty = $item->quantity;
            $item->releaseStock($quantity);

            StockMovement::create([
                'tenant_id'         => $item->tenant_id,
                'inventory_item_id' => $item->id,
                'type'              => 'release',
                'quantity'          => $quantity,
                'before_quantity'   => $beforeQty,
                'after_quantity'    => $item->fresh()->quantity,
                'reference_type'    => 'order',
                'reference_id'      => $referenceId,
                'notes'             => "Stock released from order {$referenceId} (cancellation/rollback)",
            ]);

            return $item->fresh() ?? $item;
        });
    }

    /**
     * Deduct stock on fulfillment with audit trail.
     */
    public function deductStock(string $itemId, int $quantity, string $referenceId): InventoryItem
    {
        return DB::transaction(function () use ($itemId, $quantity, $referenceId): InventoryItem {
            /** @var InventoryItem $item */
            $item = $this->newQuery()->lockForUpdate()->findOrFail($itemId);

            $beforeQty = $item->quantity;
            $item->deductStock($quantity);

            StockMovement::create([
                'tenant_id'         => $item->tenant_id,
                'inventory_item_id' => $item->id,
                'type'              => 'out',
                'quantity'          => $quantity,
                'before_quantity'   => $beforeQty,
                'after_quantity'    => $item->fresh()->quantity,
                'reference_type'    => 'order',
                'reference_id'      => $referenceId,
                'notes'             => "Stock deducted for fulfilled order {$referenceId}",
            ]);

            return $item->fresh() ?? $item;
        });
    }

    /**
     * Get items at or below reorder point.
     */
    public function getLowStockItems(string $tenantId): Collection
    {
        return $this->newQuery()
            ->where('tenant_id', $tenantId)
            ->lowStock()
            ->active()
            ->get();
    }
}
