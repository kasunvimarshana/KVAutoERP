<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Inventory\Entities\Inventory;
use App\Domain\Inventory\Repositories\InventoryRepositoryInterface;
use App\Infrastructure\Persistence\BaseRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EloquentInventoryRepository extends BaseRepository implements InventoryRepositoryInterface
{
    protected array $searchableColumns  = ['name', 'sku', 'description', 'category'];
    protected array $filterableColumns  = ['status', 'category', 'location', 'tenant_id'];
    protected array $sortableColumns    = ['name', 'sku', 'quantity', 'unit_price', 'category', 'created_at'];

    public function __construct(Inventory $model)
    {
        parent::__construct($model);
    }

    // ─── InventoryRepositoryInterface ────────────────────────────────────────

    public function findBySku(string $sku, string $tenantId): ?Inventory
    {
        $result = $this->query
            ->where('tenant_id', $tenantId)
            ->where('sku', $sku)
            ->first();

        $this->resetQuery();

        return $result;
    }

    public function getLowStockItems(string $tenantId): \Illuminate\Support\Collection
    {
        $result = $this->query
            ->where('tenant_id', $tenantId)
            ->whereColumn('quantity', '<=', 'min_stock_level')
            ->where('status', 'active')
            ->orderBy('quantity')
            ->get();

        $this->resetQuery();

        return $result;
    }

    /**
     * Atomically update the stock quantity.
     *
     * @param  string  $operation  'set' | 'increment' | 'decrement'
     */
    public function updateStock(string $id, int $quantity, string $operation = 'set'): bool
    {
        try {
            return DB::transaction(function () use ($id, $quantity, $operation): bool {
                /** @var Inventory $item */
                $item = $this->model->lockForUpdate()->findOrFail($id);

                match ($operation) {
                    'increment' => $item->quantity += $quantity,
                    'decrement' => $item->quantity = max(0, $item->quantity - $quantity),
                    default     => $item->quantity = $quantity,
                };

                return $item->save();
            });
        } catch (\Throwable $e) {
            Log::error("[EloquentInventoryRepository] updateStock failed for id={$id}: {$e->getMessage()}");
            return false;
        } finally {
            $this->resetQuery();
        }
    }

    /**
     * Reserve stock atomically. Returns false if insufficient available stock.
     */
    public function reserveStock(string $id, int $quantity): bool
    {
        try {
            return DB::transaction(function () use ($id, $quantity): bool {
                /** @var Inventory $item */
                $item = $this->model->lockForUpdate()->findOrFail($id);

                $available = $item->quantity - $item->reserved_quantity;

                if ($available < $quantity) {
                    return false;
                }

                $item->reserved_quantity += $quantity;

                return $item->save();
            });
        } catch (\Throwable $e) {
            Log::error("[EloquentInventoryRepository] reserveStock failed for id={$id}: {$e->getMessage()}");
            return false;
        } finally {
            $this->resetQuery();
        }
    }

    /**
     * Release reserved stock atomically.
     */
    public function releaseStock(string $id, int $quantity): bool
    {
        try {
            return DB::transaction(function () use ($id, $quantity): bool {
                /** @var Inventory $item */
                $item = $this->model->lockForUpdate()->findOrFail($id);

                $item->reserved_quantity = max(0, $item->reserved_quantity - $quantity);

                return $item->save();
            });
        } catch (\Throwable $e) {
            Log::error("[EloquentInventoryRepository] releaseStock failed for id={$id}: {$e->getMessage()}");
            return false;
        } finally {
            $this->resetQuery();
        }
    }

    public function getByCategory(string $category, string $tenantId): \Illuminate\Support\Collection
    {
        $result = $this->query
            ->where('tenant_id', $tenantId)
            ->where('category', $category)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $this->resetQuery();

        return $result;
    }
}
