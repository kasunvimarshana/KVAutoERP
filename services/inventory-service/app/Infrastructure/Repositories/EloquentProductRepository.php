<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Domain\Inventory\Entities\Product;
use App\Domain\Inventory\Entities\StockMovement;
use App\Domain\Inventory\Enums\StockMovementType;
use App\Domain\Inventory\Repositories\ProductRepositoryInterface;
use App\Infrastructure\Persistence\Models\Product as ProductModel;
use App\Infrastructure\Persistence\Models\StockMovement as StockMovementModel;
use App\Shared\Base\BaseRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Eloquent implementation of ProductRepositoryInterface.
 *
 * All stock mutation operations use database transactions with
 * pessimistic locking to ensure consistency under concurrent requests.
 */
final class EloquentProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    protected string $modelClass = ProductModel::class;

    /** @var array<string> Columns used for full-text search. */
    protected array $searchableColumns = ['name', 'sku', 'description', 'barcode'];

    // ─── ProductRepositoryInterface ──────────────────────────────────────────

    public function findBySku(string $sku, string $tenantId): ?Product
    {
        $row = $this->newQuery()
            ->where('tenant_id', $tenantId)
            ->where('sku', strtoupper($sku))
            ->first();

        return $row ? Product::fromArray($this->loadWithCategory($row->toArray())) : null;
    }

    public function findByCategory(string $categoryId, string $tenantId, array $filters = []): array
    {
        $query = $this->newQuery()
            ->where('tenant_id', $tenantId)
            ->where('category_id', $categoryId);

        $query = $this->applyFilters($query, $filters);

        return $query
            ->get()
            ->map(fn ($row) => Product::fromArray($row->toArray()))
            ->all();
    }

    public function findLowStock(string $tenantId): array
    {
        return $this->newQuery()
            ->where('tenant_id', $tenantId)
            ->whereRaw('stock_quantity <= min_stock_level AND min_stock_level > 0')
            ->where('is_active', true)
            ->get()
            ->map(fn ($row) => Product::fromArray($row->toArray()))
            ->all();
    }

    public function findOutOfStock(string $tenantId): array
    {
        return $this->newQuery()
            ->where('tenant_id', $tenantId)
            ->where('stock_quantity', 0)
            ->where('is_active', true)
            ->get()
            ->map(fn ($row) => Product::fromArray($row->toArray()))
            ->all();
    }

    /**
     * Apply a stock change and record the movement, all within a transaction.
     *
     * For ADJUSTMENT type, $quantity is treated as the new absolute value.
     * For all other types, $quantity is applied as given (caller decides sign).
     */
    public function updateStock(
        string $productId,
        int $quantity,
        string $type,
        string $reference,
        string $reason,
        string $performedBy,
    ): StockMovement {
        return DB::transaction(function () use (
            $productId, $quantity, $type, $reference, $reason, $performedBy
        ): StockMovement {
            /** @var ProductModel $product */
            $product = $this->newQuery()
                ->lockForUpdate()
                ->findOrFail($productId);

            $previousQty = $product->stock_quantity;

            $movementType = StockMovementType::from($type);

            $newQty = match ($movementType) {
                StockMovementType::ADJUSTMENT                          => $quantity,
                StockMovementType::IN,
                StockMovementType::RELEASE,
                StockMovementType::RETURN                              => $previousQty + $quantity,
                StockMovementType::OUT,
                StockMovementType::RESERVATION,
                StockMovementType::DAMAGE                              => max(0, $previousQty - $quantity),
            };

            $product->stock_quantity = $newQty;
            $product->save();

            $movementRow = StockMovementModel::create([
                'id'                => Str::uuid()->toString(),
                'tenant_id'         => $product->tenant_id,
                'product_id'        => $productId,
                'type'              => $type,
                'quantity'          => abs($quantity),
                'reference'         => $reference,
                'reason'            => $reason,
                'previous_quantity' => $previousQty,
                'new_quantity'      => $newQty,
                'performed_by'      => $performedBy,
            ]);

            return StockMovement::fromArray($movementRow->toArray());
        });
    }

    /**
     * Atomically reserve stock using pessimistic locking.
     *
     * @throws RuntimeException When insufficient available stock.
     */
    public function reserveStock(string $productId, int $quantity, string $orderId): bool
    {
        return DB::transaction(function () use ($productId, $quantity, $orderId): bool {
            /** @var ProductModel $product */
            $product = $this->newQuery()
                ->lockForUpdate()
                ->findOrFail($productId);

            $available = $product->stock_quantity - $product->reserved_quantity;

            if ($available < $quantity) {
                throw new RuntimeException(
                    "Insufficient stock for product {$productId}. " .
                    "Available: {$available}, Requested: {$quantity}."
                );
            }

            $product->reserved_quantity += $quantity;
            $product->save();

            // Record the reservation movement.
            StockMovementModel::create([
                'id'                => Str::uuid()->toString(),
                'tenant_id'         => $product->tenant_id,
                'product_id'        => $productId,
                'type'              => StockMovementType::RESERVATION->value,
                'quantity'          => $quantity,
                'reference'         => $orderId,
                'reason'            => 'Stock reserved for order',
                'previous_quantity' => $product->stock_quantity,
                'new_quantity'      => $product->stock_quantity,
                'performed_by'      => 'system',
            ]);

            return true;
        });
    }

    /**
     * Release previously-reserved stock within a transaction.
     */
    public function releaseStock(string $productId, int $quantity, string $orderId): bool
    {
        return DB::transaction(function () use ($productId, $quantity, $orderId): bool {
            /** @var ProductModel $product */
            $product = $this->newQuery()
                ->lockForUpdate()
                ->findOrFail($productId);

            $product->reserved_quantity = max(0, $product->reserved_quantity - $quantity);
            $product->save();

            // Record the release movement.
            StockMovementModel::create([
                'id'                => Str::uuid()->toString(),
                'tenant_id'         => $product->tenant_id,
                'product_id'        => $productId,
                'type'              => StockMovementType::RELEASE->value,
                'quantity'          => $quantity,
                'reference'         => $orderId,
                'reason'            => 'Stock released from order',
                'previous_quantity' => $product->stock_quantity,
                'new_quantity'      => $product->stock_quantity,
                'performed_by'      => 'system',
            ]);

            return true;
        });
    }

    public function bulkUpdatePrices(array $updates, string $tenantId): int
    {
        $count = 0;

        DB::transaction(function () use ($updates, $tenantId, &$count): void {
            foreach ($updates as $update) {
                $affected = $this->newQuery()
                    ->where('tenant_id', $tenantId)
                    ->where('id', $update['product_id'])
                    ->update(array_filter([
                        'price'      => $update['price'] ?? null,
                        'cost_price' => $update['cost_price'] ?? null,
                    ]));

                $count += $affected;
            }
        });

        return $count;
    }

    // ─── RepositoryInterface overrides ───────────────────────────────────────

    public function findById(string|int $id): ?array
    {
        $row = $this->newQuery()->with('category')->find($id);

        return $row ? $this->loadWithCategory($row->toArray()) : null;
    }

    public function create(array $data): array
    {
        $data['id'] = $data['id'] ?? Str::uuid()->toString();
        $data['sku'] = strtoupper($data['sku'] ?? '');

        return parent::create($data);
    }

    // ─── Private helpers ─────────────────────────────────────────────────────

    /**
     * Ensure category data is embedded in the product array for entity hydration.
     *
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function loadWithCategory(array $row): array
    {
        if (!empty($row['category'])) {
            return $row;
        }

        if (!empty($row['category_id'])) {
            $catRow = \App\Infrastructure\Persistence\Models\Category::find($row['category_id']);
            if ($catRow) {
                $row['category'] = $catRow->toArray();
            }
        }

        return $row;
    }
}
