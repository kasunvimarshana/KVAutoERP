<?php
namespace App\Services;

use App\DTOs\InventoryDTO;
use App\Events\InventoryUpdated;
use App\Events\StockLow;
use App\Models\Inventory;
use App\Models\StockMovement;
use App\Repositories\InventoryRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryService
{
    public function __construct(
        private readonly InventoryRepository  $inventoryRepository,
        private readonly ProductServiceClient $productServiceClient,
    ) {}

    public function listInventory(string $tenantId, array $filters = [], int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        $repo  = $this->inventoryRepository->withTenant($tenantId);
        $query = $repo->newQuery();

        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (!empty($filters['warehouse_location'])) {
            $query->where('warehouse_location', 'LIKE', '%' . $filters['warehouse_location'] . '%');
        }

        if (!empty($filters['low_stock'])) {
            $query->whereColumn('quantity', '<=', 'min_level');
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function getInventory(string $tenantId, string $inventoryId): ?InventoryDTO
    {
        $inventory = $this->inventoryRepository->withTenant($tenantId)->find($inventoryId);
        if ($inventory === null) {
            return null;
        }
        return InventoryDTO::fromModel($inventory);
    }

    public function createInventory(string $tenantId, array $data): InventoryDTO
    {
        $inventory = DB::transaction(function () use ($tenantId, $data): Inventory {
            $inventory = $this->inventoryRepository->create([
                'tenant_id'          => $tenantId,
                'product_id'         => $data['product_id'],
                'warehouse_location' => $data['warehouse_location'] ?? null,
                'quantity'           => $data['quantity'],
                'reserved_quantity'  => $data['reserved_quantity'] ?? 0,
                'unit'               => $data['unit'] ?? null,
                'min_level'          => $data['min_level'],
                'max_level'          => $data['max_level'],
                'status'             => $data['status'] ?? 'active',
                'notes'              => $data['notes'] ?? null,
            ]);

            StockMovement::create([
                'tenant_id'     => $tenantId,
                'inventory_id'  => $inventory->id,
                'product_id'    => $inventory->product_id,
                'movement_type' => 'in',
                'quantity'      => $inventory->quantity,
                'notes'         => 'Initial stock entry',
            ]);

            event(new InventoryUpdated($inventory));

            return $inventory;
        });

        return InventoryDTO::fromModel($inventory);
    }

    public function updateInventory(string $tenantId, string $inventoryId, array $data): ?InventoryDTO
    {
        $inventory = $this->inventoryRepository->withTenant($tenantId)->find($inventoryId);
        if ($inventory === null) {
            return null;
        }

        $updated = DB::transaction(function () use ($inventory, $data): Inventory {
            $inventory->fill($data)->save();
            event(new InventoryUpdated($inventory->fresh()));
            return $inventory->fresh();
        });

        return InventoryDTO::fromModel($updated);
    }

    public function deleteInventory(string $tenantId, string $inventoryId): bool
    {
        $inventory = $this->inventoryRepository->withTenant($tenantId)->find($inventoryId);
        if ($inventory === null) {
            return false;
        }

        return DB::transaction(fn () => $this->inventoryRepository->delete($inventoryId));
    }

    public function adjustStock(string $tenantId, string $inventoryId, int $quantity, string $movementType, ?string $notes = null, ?string $referenceType = null, ?string $referenceId = null): ?InventoryDTO
    {
        $inventory = $this->inventoryRepository->withTenant($tenantId)->find($inventoryId);
        if ($inventory === null) {
            return null;
        }

        $updated = DB::transaction(function () use ($inventory, $tenantId, $quantity, $movementType, $notes, $referenceType, $referenceId): Inventory {
            $delta = match ($movementType) {
                'in'         => abs($quantity),
                'out'        => -abs($quantity),
                'adjustment' => $quantity,
                'transfer'   => $quantity,
                default      => $quantity,
            };

            $newQuantity = max(0, $inventory->quantity + $delta);
            $inventory->update(['quantity' => $newQuantity]);

            StockMovement::create([
                'tenant_id'      => $tenantId,
                'inventory_id'   => $inventory->id,
                'product_id'     => $inventory->product_id,
                'movement_type'  => $movementType,
                'quantity'       => abs($quantity),
                'reference_type' => $referenceType,
                'reference_id'   => $referenceId,
                'notes'          => $notes,
            ]);

            $fresh = $inventory->fresh();
            event(new InventoryUpdated($fresh));

            if ($fresh->isLowStock()) {
                event(new StockLow($fresh));
            }

            return $fresh;
        });

        return InventoryDTO::fromModel($updated);
    }

    public function getByProduct(string $tenantId, string $productId): array
    {
        $inventories = $this->inventoryRepository->withTenant($tenantId)->findByProduct($productId);
        return $inventories->map(fn ($inv) => InventoryDTO::fromModel($inv)->toArray())->all();
    }

    public function listWithProductDetails(string $tenantId, int $perPage = 15, int $page = 1): array
    {
        $paginator   = $this->inventoryRepository->withTenant($tenantId)->getWithPagination($perPage, $page);
        $inventories = collect($paginator->items());

        $productIds   = $inventories->pluck('product_id')->unique()->values()->all();
        $productsData = $this->productServiceClient->getProducts($productIds, $tenantId);
        $productsMap  = collect($productsData)->keyBy('id');

        $items = $inventories->map(function ($inv) use ($productsMap) {
            $productData = $productsMap->get($inv->product_id);
            return InventoryDTO::fromModel($inv, $productData)->toArray();
        })->all();

        return [
            'data' => $items,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
            ],
        ];
    }

    public function filterByProductName(string $tenantId, string $productName, int $perPage = 15, int $page = 1): array
    {
        $products = $this->productServiceClient->searchByName($productName, $tenantId);

        if (empty($products)) {
            return [
                'data' => [],
                'meta' => ['current_page' => 1, 'per_page' => $perPage, 'total' => 0, 'last_page' => 1],
            ];
        }

        $productIds  = array_column($products, 'id');
        $productsMap = collect($products)->keyBy('id');

        $paginator = $this->inventoryRepository->withTenant($tenantId)
            ->newQuery()
            ->whereIn('product_id', $productIds)
            ->paginate($perPage, ['*'], 'page', $page);

        $items = collect($paginator->items())->map(function ($inv) use ($productsMap) {
            $productData = $productsMap->get($inv->product_id);
            return InventoryDTO::fromModel($inv, $productData)->toArray();
        })->all();

        return [
            'data' => $items,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
            ],
        ];
    }
}
